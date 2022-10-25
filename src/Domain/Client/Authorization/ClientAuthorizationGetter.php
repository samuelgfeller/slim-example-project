<?php

namespace App\Domain\Client\Authorization;

use App\Domain\Authorization\Privilege;

/**
 * The client should know when to display edit and delete icons
 * Admins can edit all notes, users only their own
 */
class ClientAuthorizationGetter
{
    public function __construct(
        private readonly ClientAuthorizationChecker $clientAuthorizationChecker,
    ) {
    }

    /**
     * Checks if authenticated user is allowed to update or read given column
     *
     * @param string $column
     * @param int $clientOwnerId
     * @return Privilege
     */
    public function getUpdatePrivilegeForClientColumn(string $column, int $clientOwnerId): Privilege
    {
        // Check if given value may be updated by authenticated user (value does not matter as keys are relevant)
         if ($this->clientAuthorizationChecker->isGrantedToUpdate([$column => 'value'], $clientOwnerId, false)) {
             return Privilege::UPDATE;
         }
         return Privilege::NONE;
    }
}