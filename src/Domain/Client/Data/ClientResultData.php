<?php

namespace App\Domain\Client\Data;

use App\Domain\Authorization\Privilege;
use App\Domain\Note\Data\NoteData;
use App\Domain\Note\Data\NoteResultData;

/**
 * Aggregate DTO to store ClientData combined with
 * some linked (aggregate) tables.
 * Used as result DTO when access to aggregate
 * details is relevant like client read.
 */
class ClientResultData extends ClientData
{
    /** @var NoteResultData[]|null */
    public ?array $notes = null;
    // Amount of notes for the client to know how many content placeholders to display
    public ?int $notesAmount = null;
    // As this below is only relevant for client read, this ClientResult data class could be renamed into ClientListResult
    // and a new class ClientReadResultAggregateData could be created extending this one as it contains more attributes
    public ?NoteData $mainNoteData = null; // Main note data

    // Client main data privilege (first-, second name, phone, email, location)
    public ?Privilege $mainDataPrivilege = null;
    public ?Privilege $clientStatusPrivilege = null;
    public ?Privilege $assignedUserPrivilege = null;
    public ?Privilege $noteCreatePrivilege = null;

    /**
     * Client Data constructor.
     *
     * @param array $clientResultData
     *
     * @throws \Exception
     */
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
            'notes' => $this->notes,
            'notesAmount' => $this->notesAmount,
            'mainNoteData' => $this->mainNoteData,

            'mainDataPrivilege' => $this->mainDataPrivilege?->value,
            'clientStatusPrivilege' => $this->clientStatusPrivilege?->value,
            'assignedUserPrivilege' => $this->assignedUserPrivilege?->value,
            'noteCreatePrivilege' => $this->noteCreatePrivilege?->value,
        ]);
    }
    // No need for toArrayForDatabase() as this is a result DTO
}
