<?php


namespace App\Domain\Note\Service;


use App\Domain\Note\Data\NoteData;
use App\Infrastructure\Note\NoteCreatorRepository;
use App\Infrastructure\User\UserFinderRepository;

class NoteCreator
{

    public function __construct(
        private readonly NoteValidator $noteValidator,
        private readonly NoteCreatorRepository $noteCreatorRepository,
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