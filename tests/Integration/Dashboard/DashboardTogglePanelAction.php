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
use Selective\TestTrait\Traits\RouteTestTrait;

class DashboardTogglePanelAction extends TestCase
{
    use AppTestTrait;
    use DatabaseTestTrait;
    use FixtureTestTrait;
    use RouteTestTrait;
    use HttpJsonTestTrait;
    use DatabaseExtensionTestTrait;

    /**
     * Test that when user clicks to enable 2 panels it is
     * saved in the database table user_filter_setting.
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     *
     * @return void
     */
    public function testDashboardTogglePanelActionAuthenticated(): void
    {
        // Insert linked and authenticated user
        $userId = $this->insertFixturesWithAttributes([], UserFixture::class)['id'];

        // Simulate logged-in user with logged-in user id
        $this->container->get(SessionInterface::class)->set('user_id', $userId);

        $request = $this->createJsonRequest(
            'PUT',
            $this->urlFor('dashboard-toggle-panel'),
            ['panelIds' => json_encode(['unassigned-panel', 'assigned-to-me-panel'])]
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
