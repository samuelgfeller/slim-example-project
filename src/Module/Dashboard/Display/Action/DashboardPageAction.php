<?php

namespace App\Module\Dashboard\Display\Action;

use App\Core\Application\Responder\TemplateRenderer;
use App\Module\Dashboard\Display\Service\DashboardPanelProvider;
use App\Module\FilterSetting\Enum\FilterModule;
use App\Module\FilterSetting\Find\Service\FilterSettingFinder;
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
        array $args,
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
