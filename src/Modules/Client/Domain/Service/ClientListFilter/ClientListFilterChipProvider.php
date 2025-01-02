<?php

namespace App\Modules\Client\Domain\Service\ClientListFilter;

use App\Modules\Authorization\Service\AuthorizationChecker;
use App\Modules\Client\Repository\ClientStatus\ClientStatusFinderRepository;
use App\Modules\FilterSetting\Data\FilterData;
use App\Modules\FilterSetting\Enum\FilterModule;
use App\Modules\FilterSetting\Service\FilterSettingFinder;
use App\Modules\User\Enum\UserRole;
use App\Modules\User\Repository\UserFinderRepository;
use App\Modules\User\Service\UserNameAbbreviator;
use Odan\Session\SessionInterface;

final readonly class ClientListFilterChipProvider
{
    public function __construct(
        private ClientStatusFinderRepository $clientStatusFinderRepository,
        private UserNameAbbreviator $userNameAbbreviator,
        private UserFinderRepository $userFinderRepository,
        private SessionInterface $session,
        private AuthorizationChecker $authorizationChecker,
        private FilterSettingFinder $filterSettingFinder,
    ) {
    }

    /**
     * Returns active and inactive filters.
     *
     * @return array{
     *     active: array<string, FilterData[]>,
     *     inactive: array<string, FilterData[]>
     *     }
     */
    public function getActiveAndInactiveClientListFilters(): array
    {
        return $this->filterSettingFinder->getActiveAndInactiveFilters(
            $this->getClientListFilters(),
            FilterModule::CLIENT_LIST
        );
    }

    /**
     * Returns default filters.
     *
     * @return FilterData[]
     */
    private function getClientListFilters(): array
    {
        $loggedInUserId = $this->session->get('user_id');

        // Basic client filters
        $allClientFilters = [
            // Category
            'unassigned' => new FilterData([
                'name' => __('Unassigned'),
                'paramName' => 'user',
                'paramValue' => null,
                'category' => null,
                'authorized' => $this->authorizationChecker->isAuthorizedByRole(UserRole::NEWCOMER),
            ]),
            'assigned_to_me' => new FilterData([
                'name' => __('Assigned to me'),
                'paramName' => 'user',
                'paramValue' => $loggedInUserId,
                'category' => null,
                'authorized' => $this->authorizationChecker->isAuthorizedByRole(UserRole::NEWCOMER),
            ]),
            'deleted' => new FilterData([
                'name' => __('Deleted'),
                'paramName' => 'deleted',
                'paramValue' => 1,
                'category' => null,
                'authorized' => $this->authorizationChecker->isAuthorizedByRole(
                    UserRole::MANAGING_ADVISOR
                ),
            ]),
        ];
        // Add all statuses to filters
        $clientStatuses = $this->clientStatusFinderRepository->findAllClientStatusesMappedByIdName();
        foreach ($clientStatuses as $id => $name) {
            $allClientFilters["status_$id"] = new FilterData([
                'name' => $name,
                'paramName' => 'status',
                'paramValue' => $id,
                'category' => 'Status',
                'authorized' => $this->authorizationChecker->isAuthorizedByRole(UserRole::NEWCOMER),
            ]);
        }

        // Add all users with the correct chip label and category
        $abbreviatedUserNames = $this->userNameAbbreviator->abbreviateUserNames(
            $this->userFinderRepository->findAllUsers()
        );
        foreach ($abbreviatedUserNames as $userId => $name) {
            // All users except authenticated user as there is already a filter "Assigned to me"
            if ($userId !== $loggedInUserId) {
                $allClientFilters["user_$userId"] = new FilterData([
                    'name' => __('Assigned to') . ' ' . $name,
                    'paramName' => 'user',
                    'paramValue' => $userId,
                    'category' => 'Other user',
                    'authorized' => $this->authorizationChecker->isAuthorizedByRole(UserRole::NEWCOMER),
                ]);
            }
        }
        $allClientFilters['deleted_assigned_user'] = new FilterData([
            'name' => __('Deleted assigned user'),
            'paramName' => 'deleted-assigned-user',
            'paramValue' => '1',
            'category' => 'Other user',
            'authorized' => $this->authorizationChecker->isAuthorizedByRole(
                UserRole::ADVISOR
            ),
        ]);

        return $allClientFilters;
    }
}
