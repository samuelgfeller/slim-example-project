<?php

namespace App\Module\User\Service\Authorization;

use App\Module\Authorization\Enum\Privilege;

/**
 * This class is responsible for determining the level of privileges a user has.
 */
final readonly class UserPrivilegeDeterminer
{
    public function __construct(
        private UserPermissionVerifier $userPermissionVerifier,
    ) {
    }

    /**
     * Checks if authenticated user is allowed to update user roles or only read them.
     *
     * @param array $grantedUserRoles
     *
     * @return string
     */
    public function getUserRoleAssignmentPrivilege(array $grantedUserRoles): string
    {
        // If there are more available roles than the attributed one, it means that user has the privilege to update roles
        if (count($grantedUserRoles) > 1) {
            return Privilege::CRU->name;
        }

        return Privilege::R->name;
    }

    /**
     * Checks if authenticated user is allowed to update or read given column
     * or delete user.
     *
     * @param int $userId
     * @param string|null $column
     *
     * @return string
     */
    public function getMutationPrivilege(int $userId, ?string $column = null): string
    {
        // Usually I'd check first against the highest privilege and if allowed, directly return otherwise continue
        // down the chain. But some authorizations are limited per column, so when a $column is provided,
        // the update privilege is checked first

        // Check if given value may be updated by authenticated user (value does not matter as keys are relevant)
        $updatePrivilege = Privilege::N;
        if ($column !== null
            && $this->userPermissionVerifier->isGrantedToUpdate([$column => 'value'], $userId, false)
        ) {
            $updatePrivilege = Privilege::CRU;
        }
        // If update privilege is set or there was no column, check for "delete"
        if (($updatePrivilege === Privilege::CRU || $column === null)
            && $this->userPermissionVerifier->isGrantedToDelete($userId, false)
        ) {
            return Privilege::CRUD->name;
        }
        // If delete privilege wasn't returned, and the authenticated is allowed to update, return update privilege
        if ($updatePrivilege === Privilege::CRU) {
            return $updatePrivilege->name;
        }

        if ($this->userPermissionVerifier->isGrantedToRead($userId, false)) {
            return Privilege::R->name;
        }

        return Privilege::N->name;
    }
}
