<?php

namespace App\Domain\Client\Data;

use App\Domain\Note\Data\NoteData;

/** Aggregate DTO to store data for client read page */
class ClientReadResult extends ClientData
{
    // Amount of notes for the client to know how many content placeholders to display
    public ?int $notesAmount = null;

    // Main note data
    public ?NoteData $mainNoteData = null;

    // If allowed to change personal client values i.e. first name, last name, location, birthdate
    public ?string $generalPrivilege = null;
    public ?string $clientStatusPrivilege = null;
    public ?string $assignedUserPrivilege = null;
    public ?string $noteCreationPrivilege = null;

    public function __construct(array $clientResultData = [])
    {
        parent::__construct($clientResultData);

        // Populate mainNote if set
        $this->mainNoteData = new NoteData([
            'id' => $clientResultData['main_note_id'] ?? null,
            'message' => $clientResultData['note_message'] ?? null,
            'hidden' => $clientResultData['note_hidden'] ?? null,
            'user_id' => $clientResultData['note_user_id'] ?? null,
            'updated_at' => $clientResultData['note_updated_at'] ?? null,
        ]);
    }

    /**
     * Define how json_encode() should serialize the object.
     *
     * @return array in the format expected by the frontend
     */
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'notesAmount' => $this->notesAmount,
            'mainNoteData' => $this->mainNoteData,

            'personalInfoPrivilege' => $this->generalPrivilege,
            'clientStatusPrivilege' => $this->clientStatusPrivilege,
            'assignedUserPrivilege' => $this->assignedUserPrivilege,
            'noteCreationPrivilege' => $this->noteCreationPrivilege,
        ]);
    }
    // No need for toArrayForDatabase() as this is a result DTO
}
