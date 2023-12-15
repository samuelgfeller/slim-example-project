<?php

namespace App\Application\Action\Dashboard;

use App\Application\Responder\TemplateRenderer;
use App\Domain\Dashboard\DashboardPanelProvider;
use App\Domain\FilterSetting\FilterModule;
use App\Domain\FilterSetting\FilterSettingFinder;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class DashboardPageAction
{
    public function __construct(
        private TemplateRenderer $templateRenderer,
        private SessionInterface $session,
        private FilterSettingFinder $filterSettingFinder,
        private DashboardPanelProvider $dashboardGetter,
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
        $o->s();
        return $this->templateRenderer->render(
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
