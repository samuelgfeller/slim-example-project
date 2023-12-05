<?php

namespace App\Domain\Authorization;

use App\Application\Data\UserNetworkSessionData;
use App\Domain\Authentication\Repository\UserRoleFinderRepository;
use App\Domain\User\Enum\UserRole;

/**
 * Default authorization checker.
 */
readonly class AuthorizationChecker
{
    public function __construct(
        private UserRoleFinderRepository $userRoleFinderRepository,
        private UserNetworkSessionData $userNetworkSessionData,
    ) {
    }

    /**
     * Returns boolean if authenticated user has given role
     * or is higher privileged.
     *
     * @param UserRole $minimalRequiredRole
     *
     * @return bool
     */
    public function isAuthorizedByRole(UserRole $minimalRequiredRole): bool
    {
        $authenticatedUserRoleHierarchy = $this->userRoleFinderRepository->getRoleHierarchyByUserId(
            $this->userNetworkSessionData->userId
        );
        // Returns array with role name as key and hierarchy as value ['role_name' => hierarchy_int]
        // * Lower hierarchy number means higher privileged role
        $userRoleHierarchies = $this->userRoleFinderRepository->getUserRolesHierarchies();

        return $authenticatedUserRoleHierarchy <= $userRoleHierarchies[$minimalRequiredRole->value];
    }
}
