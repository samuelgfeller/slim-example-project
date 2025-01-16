<?php

namespace App\Module\Note\Authorization;

use App\Module\Authorization\Enum\Privilege;
use App\Module\Note\Create\Service\NoteCreateAuthorizationChecker;
use App\Module\Note\Delete\Service\NoteDeleteAuthorizationChecker;
use App\Module\Note\Read\Service\NoteReadAuthorizationChecker;
use App\Module\Note\Update\Service\NoteUpdateAuthorizationChecker;

/**
 * For the frontend to know when to display edit and delete icons.
 * Admins can edit all notes, users only their own.
 */
final readonly class NotePrivilegeDeterminer
{
    public function __construct(
        private NoteCreateAuthorizationChecker $noteCreateAuthorizationChecker,
        private NoteReadAuthorizationChecker $noteReadAuthorizationChecker,
        private NoteUpdateAuthorizationChecker $noteUpdateAuthorizationChecker,
        private NoteDeleteAuthorizationChecker $noteDeleteAuthorizationChecker,
    ) {
    }

    /**
     * Set user mutation rights on main note from clientResultAggregateData.
     *
     * @param int|null $noteOwnerId main note owner id (null if main note doesn't exist yet)
     * @param int|null $clientOwnerId
     *
     * @return string
     */
    public function getMainNotePrivilege(?int $noteOwnerId, ?int $clientOwnerId): string
    {
        // Delete not possible with main note
        // Check first against the highest privilege, if allowed, directly return otherwise continue down the chain
        if ($this->noteUpdateAuthorizationChecker->isGrantedToUpdate(1, $noteOwnerId, $clientOwnerId, false)) {
            return Privilege::CRU->name;
        }
        if ($this->noteCreateAuthorizationChecker->isGrantedToCreate(1, $clientOwnerId, false)) {
            return Privilege::CR->name;
        }
        if ($this->noteReadAuthorizationChecker->isGrantedToRead(1, $noteOwnerId, $clientOwnerId, 0, false)) {
            return Privilege::R->name;
        }

        return Privilege::N->name;
    }

    /**
     * Get privilege of a specific note (delete, update, read).
     *
     * @param int $noteOwnerId
     * @param int|null $clientOwnerId
     * @param ?int $hidden
     * @param bool $noteDeleted
     *
     * @return string
     */
    public function getNotePrivilege(
        int $noteOwnerId,
        ?int $clientOwnerId = null,
        ?int $hidden = null,
        bool $noteDeleted = false,
    ): string {
        // Check first against the highest privilege, if allowed, directly return otherwise continue down the chain
        if ($this->noteDeleteAuthorizationChecker->isGrantedToDelete($noteOwnerId, $clientOwnerId, false)) {
            return Privilege::CRUD->name;
        }
        if ($this->noteUpdateAuthorizationChecker->isGrantedToUpdate(0, $noteOwnerId, $clientOwnerId, false)) {
            return Privilege::CRU->name;
        }
        // Create must NOT be included here as it's irrelevant on specific notes and has an impact on "READ" privilege as
        // read is lower than create in the hierarchy.
        if ($this->noteReadAuthorizationChecker->isGrantedToRead(
            0,
            $noteOwnerId,
            $clientOwnerId,
            $hidden,
            $noteDeleted,
            false
        )) {
            return Privilege::R->name;
        }

        return Privilege::N->name;
    }
}
