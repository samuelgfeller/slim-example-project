<?php

namespace App\Test\Integration\Dashboard;

use App\Test\Fixture\UserFixture;
use App\Test\Traits\AppTestTrait;
use App\Test\Traits\DatabaseExtensionTestTrait;
use App\Test\Traits\FixtureTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\TestCase;
use Selective\TestTrait\Traits\DatabaseTestTrait;
use Selective\TestTrait\Traits\HttpJsonTestTrait;
use Selective\TestTrait\Traits\HttpTestTrait;
use Selective\TestTrait\Traits\RouteTestTrait;

class DashboardTogglePanelActionTest extends TestCase
{
    use AppTestTrait;
    use HttpTestTrait;
    use DatabaseTestTrait;
    use FixtureTestTrait;
    use RouteTestTrait;
    use HttpJsonTestTrait;
    use DatabaseExtensionTestTrait;

    /**
     * Test that when user clicks to enable 2 panels it is
     * saved in the database table user_filter_setting.
     *
     * @return void
     */
    public function testDashboardTogglePanelActionAuthenticated(): void
    {
        // Insert linked and authenticated user
        $userId = $this->insertFixtureWithAttributes(new UserFixture())['id'];

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
        // Assert that panel assigned to me exists and has correct values
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
}
