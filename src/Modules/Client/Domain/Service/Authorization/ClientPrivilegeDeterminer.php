<?php

namespace App\Modules\Client\Domain\Service\Authorization;

use App\Modules\Authorization\Enum\Privilege;

/**
 * For the frontend to know when to display edit and delete icons.
 */
final readonly class ClientPrivilegeDeterminer
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
        if ($column !== null
            // Keys are relevant for the update authorization check, value doesn't matter
            && $this->clientPermissionVerifier->isGrantedToUpdate([$column => 'value'], $clientOwnerId, false)
        ) {
            return Privilege::CRU->name;
        }

        return Privilege::N->name;
    }
}
