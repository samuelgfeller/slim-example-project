<?php

namespace App\Domain\Note\Authorization;

use App\Domain\Authorization\Privilege;
use App\Infrastructure\Authentication\UserRoleFinderRepository;
use Odan\Session\SessionInterface;

/**
 * The client should know when to display edit and delete icons
 * Admins can edit all notes, users only their own
 */
class NoteAuthorizationGetter
{
    public function __construct(
        private readonly SessionInterface $session,
        private readonly UserRoleFinderRepository $userRoleFinderRepository,
        private readonly NoteAuthorizationChecker $noteAuthorizationChecker,
    ) {
    }

    /**
     * Set user mutation rights on main note from clientResultAggregateData
     *
     * @param null|int $noteOwnerId main note owner id (null if main note doesn't exist yet)
     * @param int $clientOwnerId
     * @return Privilege
     */
    public function getMainNotePrivilege(?int $noteOwnerId, int $clientOwnerId): Privilege
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
     * Get note user rights
     *
     * @param int $noteOwnerId
     * @return Privilege
     */
    public function getNotePrivilege(int $noteOwnerId): Privilege
    {
        // Check first against the highest privilege, if allowed, directly return otherwise continue down the chain
        if ($this->noteAuthorizationChecker->isGrantedToDelete($noteOwnerId, null, false)) {
            return Privilege::DELETE;
        }
        if ($this->noteAuthorizationChecker->isGrantedToUpdate(0, $noteOwnerId, null, false)) {
            return Privilege::UPDATE;
        }
        if ($this->noteAuthorizationChecker->isGrantedToCreate(0, null, false)) {
            return Privilege::CREATE;
        }
        if ($this->noteAuthorizationChecker->isGrantedToRead(0, $noteOwnerId, null, false)) {
            return Privilege::READ;
        }
        return Privilege::NONE;
    }
}