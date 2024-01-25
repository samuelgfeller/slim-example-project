<?php

namespace App\Application\Action\Dashboard;

use App\Application\Renderer\TemplateRenderer;
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

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $dashboards = $this->dashboardGetter->getAuthorizedDashboards();

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
