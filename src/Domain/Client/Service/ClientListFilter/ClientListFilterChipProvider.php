<?php

namespace App\Domain\Client\Service\ClientListFilter;

use App\Domain\Authorization\AuthorizationChecker;
use App\Domain\Client\Repository\ClientStatus\ClientStatusFinderRepository;
use App\Domain\FilterSetting\Data\FilterData;
use App\Domain\FilterSetting\FilterModule;
use App\Domain\FilterSetting\FilterSettingFinder;
use App\Domain\User\Enum\UserRole;
use App\Domain\User\Repository\UserFinderRepository;
use App\Domain\User\Service\UserNameAbbreviator;
use Odan\Session\SessionInterface;

class ClientListFilterChipProvider
{
    public function __construct(
        private readonly ClientStatusFinderRepository $clientStatusFinderRepository,
        private readonly UserNameAbbreviator $userNameAbbreviator,
        private readonly UserFinderRepository $userFinderRepository,
        private readonly SessionInterface $session,
        private readonly AuthorizationChecker $authorizationChecker,
        private readonly FilterSettingFinder $filterSettingFinder
    ) {
    }

    /**
     * Returns active and inactive filters.
     *
     * @return array{
     *     active: array{string: FilterData[]},
     *     inactive: array{string: FilterData[]}
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
                'name' => __($name),
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
