<?php

namespace App\Application\Actions\Dashboard;

use App\Application\Responder\Responder;
use App\Domain\Dashboard\DashboardPanelProvider;
use App\Domain\FilterSetting\FilterModule;
use App\Domain\FilterSetting\FilterSettingFinder;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Action.
 */
final class DashboardPageAction
{
    /**
     * The constructor.
     *
     * @param Responder $responder
     * @param SessionInterface $session
     * @param FilterSettingFinder $filterSettingFinder
     * @param DashboardPanelProvider $dashboardGetter
     */
    public function __construct(
        private readonly Responder $responder,
        private readonly SessionInterface $session,
        private readonly FilterSettingFinder $filterSettingFinder,
        private readonly DashboardPanelProvider $dashboardGetter,
    ) {
    }

    /**
     * Action.
     *
     * @param ServerRequestInterface $request The request
     * @param ResponseInterface $response The response
     * @param array $args
     *
     * @throws \Throwable
     *
     * @return ResponseInterface The response
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $dashboards = $this->dashboardGetter->getAuthorizedDashboards();

        return $this->responder->render(
            $response,
            'dashboard/dashboard.html.php',
            [
                'authenticatedUserId' => $this->session->get('user_id'),
                'dashboards' => $dashboards,
                'enabledDashboards' => $this->filterSettingFinder->findFiltersFromAuthenticatedUser(
                    FilterModule::DASHBOARD_PANEL
                ),
            ]
        );
    }
}
