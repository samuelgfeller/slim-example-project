<?php

namespace App\Test\TestCase\Dashboard;

use App\Test\Fixture\UserFixture;
use App\Test\Trait\AppTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\TestCase;
use TestTraits\Trait\DatabaseTestTrait;
use TestTraits\Trait\FixtureTestTrait;
use TestTraits\Trait\HttpJsonTestTrait;
use TestTraits\Trait\HttpTestTrait;
use TestTraits\Trait\RouteTestTrait;

class DashboardTogglePanelActionTest extends TestCase
{
    use AppTestTrait;
    use HttpTestTrait;
    use DatabaseTestTrait;
    use FixtureTestTrait;
    use RouteTestTrait;
    use HttpJsonTestTrait;

    /**
     * Test that when a user clicks to enable two panels, it is
     * saved in the database table user_filter_setting.
     *
     * @return void
     */
    public function testDashboardTogglePanelActionAuthenticated(): void
    {
        // Insert linked and authenticated user
        $userId = $this->insertFixture(UserFixture::class)['id'];

        // Simulate logged-in user by setting the user_id session variable
        $this->container->get(SessionInterface::class)->set('user_id', $userId);

        $request = $this->createJsonRequest(
            'PUT',
            $this->urlFor('dashboard-toggle-panel'),
            [
                'panelIds' => json_encode(
                    ['unassigned-panel', 'assigned-to-me-panel'],
                    JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR
                ),
            ]
        );
        $response = $this->app->handle($request);

        // Assert 200 OK
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        $this->assertTableRowCount(2, 'user_filter_setting');
        // Assert that unassigned panel exists and has correct values
        $this->assertTableRowsByColumn(
            [
                'user_id' => $userId,
                'filter_id' => 'unassigned-panel',
                'module' => 'dashboard-panel',
            ],
            'user_filter_setting',
            'filter_id',
            'unassigned-panel'
        );
        // Assert that the panel assigned to me exists and has correct values
        $this->assertTableRowsByColumn(
            [
                'user_id' => $userId,
                'filter_id' => 'assigned-to-me-panel',
                'module' => 'dashboard-panel',
            ],
            'user_filter_setting',
            'filter_id',
            'assigned-to-me-panel'
        );
    }

    /**
     * Test dashboard toggle panel action when request body is malformed.
     *
     * @return void
     */
    public function testDashboardTogglePanelMalformedRequestBody(): void
    {
        // Insert linked and authenticated user
        $userId = $this->insertFixture(UserFixture::class)['id'];

        // Simulate logged-in user by setting the user_id session variable
        $this->container->get(SessionInterface::class)->set('user_id', $userId);

        $request = $this->createJsonRequest(
            'PUT',
            $this->urlFor('dashboard-toggle-panel'),
            [
                'invalid' => 'oops',
            ]
        );

        $this->expectException(\Slim\Exception\HttpBadRequestException::class);

        $this->app->handle($request);

        // Assert flash message
        $flash = $this->container->get(SessionInterface::class)->getFlash()->all();
        self::assertStringContainsString('Malformed request body syntax', $flash['info'][0]);

        $this->assertTableRowCount(0, 'user_filter_setting');
    }
}
