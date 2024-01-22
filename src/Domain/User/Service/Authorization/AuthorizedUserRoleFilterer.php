<?php

namespace App\Domain\User\Service\Authorization;

use App\Domain\Authentication\Repository\UserRoleFinderRepository;

class AuthorizedUserRoleFilterer
{
    public function __construct(
        private readonly UserRoleFinderRepository $userRoleFinderRepository,
        private readonly UserPermissionVerifier $userPermissionVerifier,
    ) {
    }

    /**
     * Returns all roles that authenticated user is allowed to choose when
     * creating a new user.
     *
     * Note: this is not performant at all as for each user all user roles changes
     * have to be tested and isGrantedToUpdate makes four sql requests each time meaning
     * that for 10 users and 4 roles and 4 requests in the function, there will be
     * more than 120 sql requests, so this could be optimized.
     * It was done like this for simplicity as there will not be a lot of users,
     * and the user list action is quite rare and limited to only some users.
     *
     * @param int|null $attributedUserRoleId
     *
     * @return array
     */
    public function filterAuthorizedUserRoles(?int $attributedUserRoleId = null): array
    {
        $allUserRoles = $this->userRoleFinderRepository->findAllUserRolesForDropdown();
        // Available user roles for dropdown and privilege
        $grantedCreateUserRoles = [];
        foreach ($allUserRoles as $roleId => $roleName) {
            // If the role is already attributed to user the value is added so that it's displayed in the dropdown
            if (($attributedUserRoleId !== null && $roleId === $attributedUserRoleId)
                // Check if user role is granted
                || $this->userPermissionVerifier->userRoleIsGranted($roleId, $attributedUserRoleId) === true
            ) {
                $grantedCreateUserRoles[$roleId] = $roleName;
            }
        }

        return $grantedCreateUserRoles;
    }
}
