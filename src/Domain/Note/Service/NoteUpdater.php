<?php


namespace App\Domain\Note\Service;


use App\Domain\Exceptions\ForbiddenException;
use App\Domain\Note\Authorization\NoteAuthorizationChecker;
use App\Domain\Note\Data\NoteData;
use App\Infrastructure\Note\NoteUpdaterRepository;

class NoteUpdater
{

    public function __construct(
        private readonly NoteValidator $noteValidator,
        private readonly NoteUpdaterRepository $noteUpdaterRepository,
        private readonly NoteFinder $noteFinder,
        private readonly NoteAuthorizationChecker $noteAuthorizationChecker,
    ) {
    }

    /**
     * Change something or multiple things on note
     *
     * @param int $noteId id of note being changed
     * @param array|null $noteValues values that have to be changed
     * @return bool if update was successful
     */
    public function updateNote(int $noteId, null|array $noteValues): bool
    {
        // Init object for validation
        $note = new NoteData($noteValues);
        // Validate object
        $this->noteValidator->validateNoteUpdate($note);

        // Find note in db to compare its ownership
        $noteFromDb = $this->noteFinder->findNote($noteId);

        if ($this->noteAuthorizationChecker->isGrantedToUpdate($noteFromDb->userId, $noteFromDb->isMain)) {
            // The only thing that a user can change on a note is its message
            if (null !== $note->message) {
                // $updateData in own array instead of object::toArray() to be sure that only the message can be updated
                $updateData['message'] = $note->message;
                return $this->noteUpdaterRepository->updateNote($updateData, $noteId);
            }
            // Nothing was updated as message was empty
            return false;
        }

        throw new ForbiddenException('Not allowed to change note.');
    }
}