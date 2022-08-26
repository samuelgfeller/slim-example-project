<?php


namespace App\Domain\Note\Service;


use App\Domain\Note\Data\NoteData;
use App\Infrastructure\Note\NoteCreatorRepository;

class NoteCreator
{

    public function __construct(
        private NoteValidator $noteValidator,
        private NoteCreatorRepository $noteCreatorRepository
    ) { }

    /**
     * Note creation logic
     * Called by Action
     *
     * @param array $noteData
     * @param int $loggedInUserId
     *
     * @return int insert id
     */
    public function createNote(array $noteData, int $loggedInUserId): int
    {
        $note = new NoteData($noteData);
        $note->userId = $loggedInUserId;
        $this->noteValidator->validateNoteCreation($note);

        return $this->noteCreatorRepository->insertNote($note->toArray());
    }
}