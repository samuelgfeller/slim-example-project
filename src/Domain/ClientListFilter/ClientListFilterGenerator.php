<?php

namespace App\Domain\ClientListFilter;

use App\Domain\ClientListFilter\Data\ClientListFilterData;
use App\Domain\User\Enum\UserRole;
use App\Domain\User\Service\UserNameAbbreviator;
use App\Infrastructure\Authentication\UserRoleFinderRepository;
use App\Infrastructure\Client\ClientStatus\ClientStatusFinderRepository;
use App\Infrastructure\User\UserFinderRepository;
use Odan\Session\SessionInterface;

class ClientListFilterGenerator
{
    public function __construct(
        private readonly ClientStatusFinderRepository $clientStatusFinderRepository,
        private readonly UserNameAbbreviator $userNameAbbreviator,
        private readonly UserFinderRepository $userFinderRepository,
        private readonly UserRoleFinderRepository $userRoleFinderRepository,
        private readonly SessionInterface $session,
    ) {
    }

    /**
     * Check filter should be displayed to authenticated user
     *
     * @param UserRole $minimalRequiredRole
     * @return bool
     */
    private function filterIsGranted(UserRole $minimalRequiredRole): bool
    {
        $loggedInUserId = $this->session->get('user_id');
        $authenticatedUserRoleData = $this->userRoleFinderRepository->getUserRoleDataFromUser($loggedInUserId);
        /** @var array{role_name: int} $userRoleHierarchies role name as key and hierarchy value
         * lower hierarchy number means higher privilege */
        $userRoleHierarchies = $this->userRoleFinderRepository->getUserRolesHierarchies();
        return $authenticatedUserRoleData->hierarchy <= $userRoleHierarchies[$minimalRequiredRole->value];
    }

    /**
     * Returns default filters
     *
     * @return ClientListFilterData[]
     */
    public function generateClientListFilter(): array
    {
        $loggedInUserId = $this->session->get('user_id');

        // Basic client filters
        $allClientFilters = [
            // Category
                'unassigned' => new ClientListFilterData([
                    'name' => 'Unassigned',
                    'paramName' => 'user',
                    'paramValue' => null,
                    'category' => null,
                    'authorized' => $this->filterIsGranted(UserRole::NEWCOMER),
                ]),
                'assigned_to_me' => new ClientListFilterData([
                    'name' => 'Assigned to me',
                    'paramName' => "user",
                    'paramValue' => $loggedInUserId,
                    'category' => null,
                    'authorized' => $this->filterIsGranted(UserRole::NEWCOMER),
                ]),
                'deleted' => new ClientListFilterData([
                    'name' => 'Deleted',
                    'paramName' => "deleted",
                    'paramValue' => 1,
                    'category' => null,
                    'authorized' => $this->filterIsGranted(UserRole::MANAGING_ADVISOR),
                ]),
        ];
        // Add all statuses to filters
        $clientStatuses = $this->clientStatusFinderRepository->findAllClientStatusesMappedByIdName();
        foreach ($clientStatuses as $id => $name) {
            $allClientFilters["status_$id"] = new ClientListFilterData([
                'name' => $name,
                'paramName' => "status",
                'paramValue' => $id,
                'category' => 'Status',
                'authorized' => $this->filterIsGranted(UserRole::NEWCOMER)
            ]);
        }
        $abbreviatedUserNames = $this->userNameAbbreviator->abbreviateUserNames(
            $this->userFinderRepository->findAllUsers()
        );
        foreach ($abbreviatedUserNames as $userId => $name) {
            // All users except authenticated user as there is already a filter "Assigned to me"
            if ($userId !== $loggedInUserId) {
                $allClientFilters["user_$userId"] = new ClientListFilterData([
                    'name' => "Assigned to $name",
                    'paramName' => "user",
                    'paramValue' => $userId,
                    'category' => 'Other user',
                    'authorized' => $this->filterIsGranted(UserRole::NEWCOMER)
                ]);
            }
        }
        return $allClientFilters;
    }
}