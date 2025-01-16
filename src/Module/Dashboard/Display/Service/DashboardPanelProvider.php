<?php

namespace App\Module\Dashboard\Display\Service;

use App\Core\Application\Data\UserNetworkSessionData;
use App\Module\Authorization\Service\AuthorizedByRoleChecker;
use App\Module\Client\ClientStatus\Repository\ClientStatusFinderRepository;
use App\Module\Dashboard\Display\Data\DashboardData;
use App\Module\User\Enum\UserRole;

final class DashboardPanelProvider
{
    private ?int $loggedInUserId = null;

    public function __construct(
        private readonly ClientStatusFinderRepository $clientStatusFinderRepository,
        private readonly AuthorizedByRoleChecker $authorizationChecker,
        private readonly UserFilterChipProvider $userFilterChipProvider,
        UserNetworkSessionData $userNetworkSessionData,
    ) {
        $this->loggedInUserId = $userNetworkSessionData->userId;
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
            $this->clientStatusFinderRepository->findAllClientStatusesMappedByIdName(true)
        );

        // Basic client filters
        return [
            // Category
            new DashboardData([
                'title' => __('Unassigned clients'),
                'panelId' => 'unassigned-panel',
                'panelClass' => 'client-panel',
                'panelHtmlContent' => '<data data-param-name="user" data-param-value="" value=""></data>
                    <div id="client-list-wrapper-unassigned" class="client-list-wrapper"></div>',
                'authorized' => $this->authorizationChecker->isAuthorizedByRole(UserRole::NEWCOMER),
            ]),
            new DashboardData([
                'title' => __('Clients assigned to me &nbsp; — &nbsp;  action pending'),
                'panelId' => 'assigned-to-me-panel',
                'panelClass' => 'client-panel',
                'panelHtmlContent' => '<data data-param-name="user" data-param-value="' . $this->loggedInUserId . '" value=""></data>
                <data data-param-name="status" data-param-value="' . $statusesMappedByNameId['Action pending'] . '" value=""></data>
                <div id="client-list-wrapper-assigned-to-me" class="client-list-wrapper"></div>',
                'authorized' => $this->authorizationChecker->isAuthorizedByRole(UserRole::NEWCOMER),
            ]),
            new DashboardData([
                'title' => __('User activity'),
                'panelId' => 'user-activity-panel',
                'panelClass' => null,
                'panelHtmlContent' => $this->userFilterChipProvider->getUserFilterChipsHtml() .
                    '<div id="user-activity-content"></div>',
                'authorized' => $this->authorizationChecker->isAuthorizedByRole(
                    UserRole::MANAGING_ADVISOR
                ),
            ]),
            new DashboardData([
                'title' => __('New notes'),
                'panelId' => 'new-notes-panel',
                'panelClass' => 'note-panel',
                'panelHtmlContent' => '<data data-param-name="most-recent" data-param-value="10" value=""></data>
                                                <div id="note-wrapper-most-recent" class="client-note-wrapper"></div>',
                'authorized' => $this->authorizationChecker->isAuthorizedByRole(UserRole::MANAGING_ADVISOR),
            ]),
            new DashboardData([
                'title' => __('Recently assigned clients'),
                'panelId' => 'recently-assigned-panel',
                'panelClass' => 'client-panel',
                'panelHtmlContent' => '<data data-param-name="recently-assigned" data-param-value="1" value=""></data>
                                <div id="client-list-wrapper-recently-assigned" class="client-list-wrapper"></div>',
                'authorized' => $this->authorizationChecker->isAuthorizedByRole(
                    UserRole::MANAGING_ADVISOR
                ),
            ]),
        ];
    }
}
