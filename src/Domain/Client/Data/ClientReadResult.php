<?php

namespace App\Domain\Client\Data;

use App\Domain\Note\Data\NoteData;

/** Aggregate DTO to store data for client read page */
class ClientReadResult extends ClientData
{
    // Amount of notes for the client to know how many skeleton loaders to display
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
}
