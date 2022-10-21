<?php

namespace App\Domain\Client\Data;


use App\Domain\Note\Data\NoteData;
use App\Domain\Note\Data\NoteWithUserData;
use App\Domain\User\Data\MutationRights;

/**
 * Aggregate DTO to store ClientData combined with
 * some linked (aggregate) classes.
 * Used as result DTO when access to aggregate
 * details is relevant like client read.
 */
class ClientResultAggregateData extends ClientData
{

    // public ?ClientStatusData $clientStatusData;
    // public ?UserData $userData;
    /** @var NoteWithUserData[]|null $notes */
    public ?array $notes = null;
    // Amount of notes for the client to know how many content placeholders to display
    public ?int $notesAmount = null;
    // As this below is only relevant for client read, this ClientResult data class could be renamed into ClientListResult
    // and a new class ClientReadResultAggregateData could be created extending this one as it contains more attributes
    public ?NoteData $mainNoteData = null; // Main note data

    public ?MutationRights $clientStatusMutationRights = null;
    public ?MutationRights $assignedUserMutationRights = null;

    /**
     * Client Data constructor.
     * @param array $clientResultData
     */
    public function __construct(array $clientResultData = [])
    {
        parent::__construct($clientResultData);

        // Aggregate DTOs populated with values relevant to client result
        // $this->clientStatusData = new ClientStatusData([
        //     'name' => $clientResultData['status_name'] ?? null
        // ]);
        // User data populated with values relevant to client result
        // $this->userData = new UserData([
        //     'first_name' => $clientResultData['user_first_name'] ?? null,
        //     'surname' => $clientResultData['user_surname'] ?? null,
        // ]);
        // Populate mainNote if set (only when read)
        $this->mainNoteData = new NoteData([
            'id' => $clientResultData['main_note_id'] ?? null,
            'message' => $clientResultData['note_message'] ?? null,
            'user_id' => $clientResultData['note_user_id'] ?? null,
            'updated_at' => $clientResultData['note_updated_at'] ?? null,
        ]);
    }

    // No need for toArrayForDatabase() as this is a result DTO
}