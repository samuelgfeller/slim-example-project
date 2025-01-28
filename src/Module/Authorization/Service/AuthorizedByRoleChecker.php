<?php

namespace App\Module\Authorization\Service;

use App\Application\Data\UserNetworkSessionData;
use App\Module\Authorization\Repository\AuthorizationUserRoleFinderRepository;
use App\Module\User\Enum\UserRole;

/**
 * Default authorization checker.
 */
final readonly class AuthorizedByRoleChecker
{
    public function __construct(
        private AuthorizationUserRoleFinderRepository $authorizationUserRoleFinderRepository,
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
        $authenticatedUserRoleHierarchy = $this->authorizationUserRoleFinderRepository->getRoleHierarchyByUserId(
            $this->userNetworkSessionData->userId
        );
        // Returns array with role name as key and hierarchy as value ['role_name' => hierarchy_int]
        // * Lower hierarchy number means higher privileged role
        $userRoleHierarchies = $this->authorizationUserRoleFinderRepository->getUserRolesHierarchies();

        return $authenticatedUserRoleHierarchy <= $userRoleHierarchies[$minimalRequiredRole->value];
    }
}
