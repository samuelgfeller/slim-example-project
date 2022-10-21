<?php

namespace App\Domain\Client\Authorization;

use App\Domain\Client\Data\ClientResultAggregateData;
use App\Domain\Note\Authorization\NoteAuthorizationChecker;
use App\Domain\Note\Data\NoteData;
use App\Domain\User\Data\MutationRights;

/**
 * The client should know when to display edit and delete icons
 * Admins can edit all notes, users only their own
 */
class ClientAuthorizationSetter
{
    public function __construct(
        private readonly NoteAuthorizationChecker $noteAuthorizationChecker,
        private readonly ClientAuthorizationChecker $clientAuthorizationChecker,
    ) {
    }

    /**
     * Set user mutation rights on main note from clientResultAggregateData
     *
     * @param NoteData $mainNoteData object reference to be changed
     * @param int $ownerId
     * @return void original main note object is changed in this function
     */
    public function setUserRightsOnMainNote(NoteData $mainNoteData, int $ownerId): void
    {
        if ($this->noteAuthorizationChecker->isGrantedToUpdate(1, null, $ownerId)) {
            $mainNoteData->mutationRights = MutationRights::ALL;
        } else {
            $mainNoteData->mutationRights = MutationRights::READ;
        }
    }

    /**
     * Set user mutation rights for client read dropdown values (status and assigned user)
     *
     * @param ClientResultAggregateData $clientResultAggregateData
     *
     * @return void original main note object is changed in this function
     */
    public function setUserRightsForClientDropdowns(ClientResultAggregateData $clientResultAggregateData): void
    {
        // The authorization rules are in the clientAuthorizationChecker so this can be used to set mutationRights
        if ($this->clientAuthorizationChecker->isGrantedToUpdate(['client_status_id' => 1],
            $clientResultAggregateData->userId,
            false)) {
            $clientResultAggregateData->clientStatusMutationRights = MutationRights::ALL;
        } else {
            $clientResultAggregateData->clientStatusMutationRights = MutationRights::READ;
        }

        // Assigned user
        if ($this->clientAuthorizationChecker->isGrantedToUpdate(['user_id' => 1],
            $clientResultAggregateData->userId,
            false)) {
            $clientResultAggregateData->assignedUserMutationRights = MutationRights::ALL;
        } else {
            $clientResultAggregateData->assignedUserMutationRights = MutationRights::READ;
        }

    }
}