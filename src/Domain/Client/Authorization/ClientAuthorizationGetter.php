<?php

namespace App\Domain\Client\Authorization;

use App\Domain\Authorization\Privilege;

/**
 * The client should know when to display edit and delete icons
 * Admins can edit all notes, users only their own.
 */
class ClientAuthorizationGetter
{
    public function __construct(
        private readonly ClientAuthorizationChecker $clientAuthorizationChecker,
    ) {
    }

    /**
     * Checks if authenticated user is allowed to delete, update or read given column.
     *
     * @param int|null $clientOwnerId
     * @param string|null $column
     *
     * @return Privilege
     */
    public function getMutationPrivilegeForClientColumn(?int $clientOwnerId, string $column = null): Privilege
    {
        // Check first against the highest privilege, if allowed, directly return otherwise continue down the chain
        if ($this->clientAuthorizationChecker->isGrantedToDelete($clientOwnerId, false)) {
            return Privilege::DELETE;
        }
        // Value does not matter as keys are relevant
        if ($column !== null &&
            $this->clientAuthorizationChecker->isGrantedToUpdate([$column => 'value'], $clientOwnerId, false)
        ) {
            return Privilege::UPDATE;
        }

        return Privilege::NONE;
    }
}
