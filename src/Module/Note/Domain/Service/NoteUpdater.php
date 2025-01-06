<?php

namespace App\Module\Note\Domain\Service;

use App\Module\Authorization\Exception\ForbiddenException;
use App\Module\Note\Data\NoteData;
use App\Module\Note\Domain\Service\Authorization\NotePermissionVerifier;
use App\Module\Note\Repository\NoteUpdaterRepository;
use App\Module\User\Enum\UserActivity;
use App\Module\UserActivity\Service\UserActivityLogger;

final readonly class NoteUpdater
{
    public function __construct(
        private NoteValidator $noteValidator,
        private NoteUpdaterRepository $noteUpdaterRepository,
        private NoteFinder $noteFinder,
        private NotePermissionVerifier $notePermissionVerifier,
        private UserActivityLogger $userActivityLogger,
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
    public function updateNote(int $noteId, ?array $noteValues): bool
    {
        // Find note in db
        $noteFromDb = $this->noteFinder->findNote($noteId);
        // Add is_main to note object before validation as there is a difference in validation
        $noteValues['is_main'] = $noteFromDb->isMain;

        // Validate object
        $this->noteValidator->validateNoteValues($noteValues, false);

        $note = new NoteData($noteValues);

        if ($this->notePermissionVerifier->isGrantedToUpdate($noteFromDb->isMain ?? 0, $noteFromDb->userId)) {
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
            $updated = false;
            // $updateData empty string if request body is empty
            if ($updateData !== []) {
                $updated = $this->noteUpdaterRepository->updateNote($updateData, $noteId);
            }
            $this->userActivityLogger->logUserActivity(UserActivity::UPDATED, 'note', $noteId, $updateData);

            return $updated;
        }

        throw new ForbiddenException('Not allowed to change note.');
    }
}
