<?php

namespace App\Domain\ClientListFilter;

use App\Domain\User\Enum\UserRole;
use App\Domain\User\Service\UserNameAbbreviator;
use App\Infrastructure\Authentication\UserRoleFinderRepository;
use App\Infrastructure\Client\ClientListFilter\ClientListFilterFinderRepository;
use App\Infrastructure\Client\ClientStatus\ClientStatusFinderRepository;
use App\Infrastructure\User\UserFinderRepository;
use Odan\Session\SessionInterface;

class ClientListFilterFinder
{
    public function __construct(
        private readonly ClientListFilterFinderRepository $clientListFilterFinderRepository,
        private readonly SessionInterface $session,
        private readonly ClientStatusFinderRepository $clientStatusFinderRepository,
        private readonly UserNameAbbreviator $userNameAbbreviator,
        private readonly UserFinderRepository $userFinderRepository,
        private readonly UserRoleFinderRepository $userRoleFinderRepository,
    ) {
    }

    private function filterIsGranted(UserRole $minimalRequiredRole)
    {
        $loggedInUserId = $this->session->get('user_id');
        $authenticatedUserRoleData = $this->userRoleFinderRepository->getUserRoleDataFromUser($loggedInUserId);
        /** @var array{role_name: int} $userRoleHierarchies role name as key and hierarchy value
         * lower hierarchy number means higher privilege */
        $userRoleHierarchies = $this->userRoleFinderRepository->getUserRolesHierarchies();
        return $authenticatedUserRoleData->hierarchy <= $userRoleHierarchies[$minimalRequiredRole->value];
    }

    /**
     * Returns active and inactive list filters
     *
     * @return array{filterId: array{name: string, param: string, user_role: UserRole}}
     */
    public function findClientListFilters(): array
    {
        // $allClientFilters = $this->clientListFilterFinderRepository->findAllClientListFilters();
        $loggedInUserId = $this->session->get('user_id');

        // Basic client filters
        $allClientFilters = [
            'unassigned' => [
                'name' => 'Unassigned',
                'param_name' => 'user',
                'param_value' => null,
                'authorized' => $this->filterIsGranted(UserRole::NEWCOMER),
            ],
            'assigned_to_me' => [
                'name' => 'Assigned to me',
                'param_name' => "user",
                'param_value' => $loggedInUserId,
                'authorized' => $this->filterIsGranted(UserRole::NEWCOMER),
            ],
            'deleted' => [
                'name' => 'Deleted',
                'param_name' => "deleted",
                'param_value' => 1,
                'authorized' => $this->filterIsGranted(UserRole::MANAGING_ADVISOR),
            ],
        ];
        // Add all statuses to filters 
        $clientStatuses = $this->clientStatusFinderRepository->findAllClientStatusesMappedByIdName();
        foreach ($clientStatuses as $id => $name) {
            $allClientFilters["status_ $id"] = [
                'name' => $name,
                'param_name' => "status",
                'param_value' => $id,
                'authorized' => $this->filterIsGranted(UserRole::NEWCOMER)
            ];
        }
        $abbreviatedUserNames = $this->userNameAbbreviator->abbreviateUserNames(
            $this->userFinderRepository->findAllUsers()
        );
        foreach ($abbreviatedUserNames as $userId => $name) {
            // All users except authenticated user as there is already a filter "Assigned to me"
            if ($userId !== $loggedInUserId) {
                $allClientFilters["user_ $userId"] = [
                    'name' => "Assigned to $name",
                    'param_name' => "user",
                    'param_value' => $userId,
                    'authorized' => $this->filterIsGranted(UserRole::NEWCOMER)
                ];
            }
        }

        // $this->session->set('client_list_filter', [1, 2, 3]);
        $returnArray['active'] = [];
        // Check which filters are active in session
        if (($activeFilters = $this->session->get('client_list_filter')) !== null) {
            foreach ($activeFilters as $activeFilterId) {
                // Add to active filters only if it still exists and is authorized
                if (isset($allClientFilters[$activeFilterId]) && $allClientFilters[$activeFilterId]['authorized']) {
                    $returnArray['active'][$activeFilterId] = $allClientFilters[$activeFilterId];
                    // Remove filter from $allClientFilters if it's an active filter
                    unset($allClientFilters[$activeFilterId]);
                }
            }
        }
        // Add active filters to session (refresh in case there was an old filter that doesn't exist anymore)
        $this->session->set('client_list_filter', array_keys($returnArray['active']));
        // Inactive are the ones that were not added to 'active' previously
        $returnArray['inactive'] = $allClientFilters;

        return $returnArray;
    }
}