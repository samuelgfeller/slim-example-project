<?php

namespace App\Domain\Note\Service;

use App\Domain\Authentication\Exception\ForbiddenException;
use App\Domain\Note\Authorization\NoteAuthorizationChecker;
use App\Domain\Note\Data\NoteData;
use App\Domain\User\Enum\UserActivity;
use App\Domain\User\Service\UserActivityManager;
use App\Infrastructure\Note\NoteUpdaterRepository;

class NoteUpdater
{
    public function __construct(
        private readonly NoteValidator $noteValidator,
        private readonly NoteUpdaterRepository $noteUpdaterRepository,
        private readonly NoteFinder $noteFinder,
        private readonly NoteAuthorizationChecker $noteAuthorizationChecker,
        private readonly UserActivityManager $userActivityManager,
    ) {
    }

    /**
     * Change something or multiple things on note.
     *
     * @param int $noteId id of note being changed
     * @param array|null $noteValues values that have to be changed
     *
     * @return bool if update was successful
     */
    public function updateNote(int $noteId, null|array $noteValues): bool
    {
        // Init object for validation
        $note = new NoteData($noteValues);

        // Find note in db
        $noteFromDb = $this->noteFinder->findNote($noteId);
        // Add is_main to note object before validation as there is a difference
        $note->isMain = $noteFromDb->isMain;

        // Validate object
        $this->noteValidator->validateNoteUpdate($note);

        if ($this->noteAuthorizationChecker->isGrantedToUpdate($noteFromDb->isMain, $noteFromDb->userId)) {
            $updateData = [];
            // Change message
            if (null !== $note->message) {
                // $updateData in own array instead of object::toArray() to be sure that only the message can be updated
                $updateData['message'] = $note->message;
            }
            // Change if is hidden
            if (null !== $note->hidden) {
                $updateData['hidden'] = $note->hidden;
            }

            $updated = $this->noteUpdaterRepository->updateNote($updateData, $noteId);
            if ($updated) {
                $this->userActivityManager->addUserActivity(UserActivity::UPDATED, 'note', $noteId, $updateData);
            }

            return $updated;
        }

        throw new ForbiddenException('Not allowed to change note.');
    }
}
