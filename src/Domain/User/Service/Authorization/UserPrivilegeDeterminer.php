<?php

namespace App\Domain\User\Service\Authorization;

use App\Domain\Authorization\Privilege;

/**
 * This class is responsible for determining the level of privileges a user has.
 */
class UserPrivilegeDeterminer
{
    public function __construct(
        private readonly UserPermissionVerifier $userPermissionVerifier,
    ) {
    }

    /**
     * Checks if authenticated user is allowed to update user roles or only read them.
     *
     * @param array $grantedUserRoles
     *
     * @return Privilege
     */
    public function determineUserRoleAssignmentPrivilege(array $grantedUserRoles): Privilege
    {
        // If there are more available roles than the attributed one, it means that user has the privilege to update roles
        if (count($grantedUserRoles) > 1) {
            return Privilege::UPDATE;
        }

        return Privilege::READ;
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
    public function determineMutationPrivilege(int $userId, ?string $column = null): Privilege
    {
        // Usually I'd check first against the highest privilege and if allowed, directly return otherwise continue
        // down the chain. But some authorizations are limited per column, so when a $column is provided,
        // the update privilege is checked first

        // Check if given value may be updated by authenticated user (value does not matter as keys are relevant)
        $updatePrivilege = Privilege::NONE;
        if ($column !== null
            && $this->userPermissionVerifier->isGrantedToUpdate([$column => 'value'], $userId, false)
        ) {
            $updatePrivilege = Privilege::UPDATE;
        }
        // If update privilege is set or there was no column, check for "delete"
        if (($updatePrivilege === Privilege::UPDATE || $column === null)
            && $this->userPermissionVerifier->isGrantedToDelete($userId, false)
        ) {
            return Privilege::DELETE;
        }
        // If delete privilege wasn't returned, and the authenticated is allowed to update, return update privilege
        if ($updatePrivilege === Privilege::UPDATE) {
            return $updatePrivilege;
        }

        if ($this->userPermissionVerifier->isGrantedToRead($userId, false)) {
            return Privilege::READ;
        }

        return Privilege::NONE;
    }
}
