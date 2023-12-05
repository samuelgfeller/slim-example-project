<?php

namespace App\Domain\Client\Service\Authorization;

use App\Domain\Authorization\Privilege;

/**
 * The client should know when to display edit and delete icons
 * Admins can edit all notes, users only their own.
 */
readonly class ClientPrivilegeDeterminer
{
    public function __construct(
        private ClientPermissionVerifier $clientPermissionVerifier,
    ) {
    }

    /**
     * Checks if authenticated user is allowed to delete, update or read given column.
     *
     * @param int|null $clientOwnerId
     * @param string|null $column
     *
     * @return string
     */
    public function getMutationPrivilege(?int $clientOwnerId, ?string $column = null): string
    {
        // Check first against the highest privilege, if allowed, directly return otherwise continue down the chain
        if ($this->clientPermissionVerifier->isGrantedToDelete($clientOwnerId, false)) {
            return Privilege::CRUD->name;
        }
        // Value does not matter as keys are relevant
        if ($column !== null
            && $this->clientPermissionVerifier->isGrantedToUpdate([$column => 'value'], $clientOwnerId, false)
        ) {
            return Privilege::CRU->name;
        }

        return Privilege::N->name;
    }
}
