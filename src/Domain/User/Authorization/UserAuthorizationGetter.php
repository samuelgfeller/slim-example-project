<?php

namespace App\Domain\User\Authorization;

use App\Domain\Authorization\Privilege;
use App\Infrastructure\Authentication\UserRoleFinderRepository;

/**
 * The client should know when to display edit and delete icons
 * Admins can edit all notes, users only their own.
 */
class UserAuthorizationGetter
{
    public function __construct(
        private readonly UserAuthorizationChecker $userAuthorizationChecker,
        private readonly UserRoleFinderRepository $userRoleFinderRepository,
    ) {
    }

    /**
     * Returns authorized user roles for given user.
     *
     * @param array $grantedUserRoles
     *
     * @return Privilege
     */
    public function getUserRoleAttributionPrivilege(array $grantedUserRoles): Privilege
    {
        // If there are more available roles than the attributed one, it means that user has privilege to update roles
        if (count($grantedUserRoles) > 1) {
            return Privilege::UPDATE;
        }

        return Privilege::READ;
    }

    /**
     * Returns all roles that authenticated user is allowed to choose when
     * creating a new user.
     *
     * Note: this is not performant at all as for each user all user roles changes
     * have to be tested and isGrantedToUpdate makes 4 sql requests each time meaning
     * that for 10 users and 4 roles and 4 requests in the function there will be
     * more than 120 sql requests so if optimisations have to be made, here is a good place
     * to start. It is like this for simplicity as there will not be a lot of users
     * anyway and the user list action is quite rare and limited to some users.
     *
     * @param int|null $attributedUserRoleId
     *
     * @return array
     */
    public function getAuthorizedUserRoles(?int $attributedUserRoleId = null): array
    {
        $allUserRoles = $this->userRoleFinderRepository->findAllUserRolesForDropdown();
        // Available user roles for dropdown and privilege
        $grantedCreateUserRoles = [];
        foreach ($allUserRoles as $roleId => $roleName) {
            // If the role is already attributed to user the value is added so that it's displayed in the dropdown
            if (($attributedUserRoleId !== null && $roleId === $attributedUserRoleId)
                // Check if user role is granted
                || $this->userAuthorizationChecker->userRoleIsGranted($roleId, $attributedUserRoleId) === true
            ) {
                $grantedCreateUserRoles[$roleId] = $roleName;
            }
        }

        return $grantedCreateUserRoles;
    }

    /**
     * Checks if authenticated user is allowed to update or read given column
     * or delete user.
     *
     * @param int $userId
     * @param string|null $column
     *
     * @return Privilege
     */
    public function getMutationPrivilegeForUserColumn(int $userId, ?string $column = null): Privilege
    {
        // Usually I'd check first against the highest privilege and if allowed, directly return otherwise continue down the chain
        // But some authorizations are limited per column, so when a column is provided, the update privilege is checked first

        // Check if given value may be updated by authenticated user (value does not matter as keys are relevant)
        $updatePrivilege = Privilege::NONE;
        if ($column !== null &&
            $this->userAuthorizationChecker->isGrantedToUpdate([$column => 'value'], $userId, false)
        ) {
            $updatePrivilege = Privilege::UPDATE;
        }
        // If update privilege is set or there was no column, check for delete
        if (($updatePrivilege === Privilege::UPDATE || $column === null)
            && $this->userAuthorizationChecker->isGrantedToDelete($userId, false)
        ) {
            return Privilege::DELETE;
        }
        // If delete privilege wasn't returned and the authenticated is allowed to update, return update privilege
        if ($updatePrivilege === Privilege::UPDATE) {
            return $updatePrivilege;
        }

        if ($this->userAuthorizationChecker->isGrantedToRead($userId, false)) {
            return Privilege::READ;
        }

        return Privilege::NONE;
    }
}
