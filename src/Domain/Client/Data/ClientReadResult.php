<?php

namespace App\Domain\Client\Data;

use App\Domain\Note\Data\NoteData;

/**
 * Aggregate DTO to store ClientData combined with
 * some linked (aggregate) tables.
 * Used as a result DTO when access to aggregate
 * details is relevant like client read.
 */
class ClientReadResult extends ClientData
{
    // Amount of notes for the client to know how many content placeholders to display
    public ?int $notesAmount = null;
    // As this below is only relevant for client read, this ClientResult data class could be renamed into ClientListResult
    // and a new class ClientReadResultAggregateData could be created extending this one as it contains more attributes
    public ?NoteData $mainNoteData = null; // Main note data

    // Client personal info privilege (first-, second name, phone, email, location)
    public ?string $generalPrivilege = null;
    public ?string $clientStatusPrivilege = null;
    public ?string $assignedUserPrivilege = null;
    public ?string $noteCreatePrivilege = null;

    public function __construct(array $clientResultData = [])
    {
        parent::__construct($clientResultData);

        // Populate mainNote if set (only when read)
        $this->mainNoteData = new NoteData([
            'id' => $clientResultData['main_note_id'] ?? null,
            'message' => $clientResultData['note_message'] ?? null,
            'hidden' => $clientResultData['note_hidden'] ?? null,
            'user_id' => $clientResultData['note_user_id'] ?? null,
            'updated_at' => $clientResultData['note_updated_at'] ?? null,
        ]);
    }

    /**
     * Output for json_encode.
     */
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'notesAmount' => $this->notesAmount,
            'mainNoteData' => $this->mainNoteData,

            'personalInfoPrivilege' => $this->generalPrivilege,
            'clientStatusPrivilege' => $this->clientStatusPrivilege,
            'assignedUserPrivilege' => $this->assignedUserPrivilege,
            'noteCreatePrivilege' => $this->noteCreatePrivilege,
        ]);
    }
    // No need for toArrayForDatabase() as this is a result DTO
}
