<?php

namespace App\Domain\Note\Authorization;

use App\Domain\Authorization\Privilege;

/**
 * The client should know when to display edit and delete icons
 * Admins can edit all notes, users only their own.
 */
class NoteAuthorizationGetter
{
    public function __construct(
        private readonly NoteAuthorizationChecker $noteAuthorizationChecker,
    ) {
    }

    /**
     * Set user mutation rights on main note from clientResultAggregateData.
     *
     * @param int|null $noteOwnerId main note owner id (null if main note doesn't exist yet)
     * @param int|null $clientOwnerId
     *
     * @return Privilege
     */
    public function getMainNotePrivilege(?int $noteOwnerId, ?int $clientOwnerId): Privilege
    {
        // Delete not possible with main note
        // Check first against the highest privilege, if allowed, directly return otherwise continue down the chain
        if ($this->noteAuthorizationChecker->isGrantedToUpdate(1, $noteOwnerId, $clientOwnerId, false)) {
            return Privilege::UPDATE;
        }
        if ($this->noteAuthorizationChecker->isGrantedToCreate(1, $clientOwnerId, false)) {
            return Privilege::CREATE;
        }
        if ($this->noteAuthorizationChecker->isGrantedToRead(1, $noteOwnerId, $clientOwnerId, false)) {
            return Privilege::READ;
        }

        return Privilege::NONE;
    }

    /**
     * Get privilege of a specific note (delete, update, read).
     *
     * @param int $noteOwnerId
     * @param int|null $clientOwnerId
     * @param ?int $hidden
     *
     * @return Privilege
     */
    public function getNotePrivilege(int $noteOwnerId, ?int $clientOwnerId = null, ?int $hidden = null): Privilege
    {
        // Check first against the highest privilege, if allowed, directly return otherwise continue down the chain
        if ($this->noteAuthorizationChecker->isGrantedToDelete($noteOwnerId, $clientOwnerId, false)) {
            return Privilege::DELETE;
        }
        if ($this->noteAuthorizationChecker->isGrantedToUpdate(0, $noteOwnerId, $clientOwnerId, false)) {
            return Privilege::UPDATE;
        }
        // Create must NOT be included here as it's irrelevant on specific notes and has an impact on "READ" privilege as
        // read is lower than create in the hierarchy.
        if ($this->noteAuthorizationChecker->isGrantedToRead(0, $noteOwnerId, $clientOwnerId, $hidden, false)) {
            return Privilege::READ;
        }

        return Privilege::NONE;
    }
}
