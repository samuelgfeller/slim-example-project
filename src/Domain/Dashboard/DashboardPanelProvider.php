<?php

namespace App\Domain\Dashboard;

use App\Domain\Authorization\AuthorizationChecker;
use App\Domain\Dashboard\Data\DashboardData;
use App\Domain\Dashboard\Panel\UserFilterChipProvider;
use App\Domain\User\Enum\UserRole;
use App\Infrastructure\Client\ClientStatus\ClientStatusFinderRepository;
use Odan\Session\SessionInterface;

class DashboardPanelProvider
{
    public function __construct(
        private readonly ClientStatusFinderRepository $clientStatusFinderRepository,
        private readonly AuthorizationChecker $authorizationChecker,
        private readonly SessionInterface $session,
        private readonly UserFilterChipProvider $userFilterChipProvider,
    ) {
    }

    /**
     * Returns authorized dashboards.
     *
     * @return DashboardData[]
     */
    public function getAuthorizedDashboards(): array
    {
        $authorizedDashboards = [];
        foreach ($this->getDashboards() as $dashboard) {
            if ($dashboard->authorized) {
                $authorizedDashboards[] = $dashboard;
            }
        }

        return $authorizedDashboards;
    }

    /**
     * Returns default dashboards.
     *
     * @return DashboardData[]
     */
    private function getDashboards(): array
    {
        $statusesMappedByNameId = array_flip(
            $this->clientStatusFinderRepository->findAllClientStatusesMappedByIdName()
        );
        $loggedInUserId = $this->session->get('user_id');

        // Basic client filters
        return [
            // Category
            new DashboardData([
                'title' => 'Unassigned clients',
                'panelId' => 'unassigned-panel',
                'panelClass' => 'client-panel',
                'panelHtmlContent' => '<data data-param-name="user" data-param-value="" value=""></data>
                    <div id="client-wrapper-unassigned" class="client-wrapper"></div>',
                'authorized' => $this->authorizationChecker->isAuthorizedByRole(UserRole::NEWCOMER),
            ]),
            new DashboardData([
                'title' => 'Clients assigned to me &nbsp; — &nbsp;  action pending',
                'panelId' => 'assigned-to-me-panel',
                'panelClass' => 'client-panel',
                'panelHtmlContent' => '<data data-param-name="user" data-param-value="' . $loggedInUserId . '" value=""></data>
                <data data-param-name="status" data-param-value="' . $statusesMappedByNameId['Action pending'] . '" value=""></data>
                <div id="client-wrapper-assigned-to-me" class="client-wrapper"></div>',
                'authorized' => $this->authorizationChecker->isAuthorizedByRole(UserRole::NEWCOMER),
            ]),
            new DashboardData([
                'title' => 'User activity',
                'panelId' => 'user-activity-panel',
                'panelClass' => null,
                'panelHtmlContent' => $this->userFilterChipProvider->getUserFilterChipsHtml() .
                    '<div id="user-activity-content"></div>',
                'authorized' => $this->authorizationChecker->isAuthorizedByRole(
                    UserRole::MANAGING_ADVISOR
                ),
            ]),
            new DashboardData([
                'title' => 'New notes',
                'panelId' => 'new-notes-panel',
                'panelClass' => 'note-panel',
                'panelHtmlContent' => '<data data-param-name="most-recent" data-param-value="1" value=""></data>
                                                <div id="note-wrapper-most-recent" class="client-note-wrapper"></div>',
                'authorized' => $this->authorizationChecker->isAuthorizedByRole(UserRole::MANAGING_ADVISOR),
            ]),
            new DashboardData([
                'title' => 'Recently assigned clients',
                'panelId' => 'recently-assigned-panel',
                'panelClass' => 'client-panel',
                'panelHtmlContent' => '<data data-param-name="recently-assigned" data-param-value="1" value=""></data>
                                <div id="client-wrapper-recently-assigned" class="client-wrapper"></div>',
                'authorized' => $this->authorizationChecker->isAuthorizedByRole(
                    UserRole::MANAGING_ADVISOR
                ),
            ]),
        ];
    }
}
