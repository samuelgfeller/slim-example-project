<?php

namespace App\Domain\Note\Service;

use App\Domain\Authentication\Exception\ForbiddenException;
use App\Domain\Exception\InvalidOperationException;
use App\Domain\Note\Repository\NoteDeleterRepository;
use App\Domain\Note\Service\Authorization\NotePermissionVerifier;
use App\Domain\User\Enum\UserActivity;
use App\Domain\UserActivity\Service\UserActivityLogger;

final readonly class NoteDeleter
{
    public function __construct(
        private NoteDeleterRepository $noteDeleterRepository,
        private NoteFinder $noteFinder,
        private NotePermissionVerifier $notePermissionVerifier,
        private UserActivityLogger $userActivityLogger,
    ) {
    }

    /**
     * Delete one note logic.
     *
     * @param int $noteId
     *
     * @return bool
     */
    public function deleteNote(int $noteId): bool
    {
        // Find note in db to get its ownership
        $noteFromDb = $this->noteFinder->findNote($noteId);

        // There is no option in GUI to delete main note so this is an invalid operation
        if ($noteFromDb->isMain === 1) {
            // Asserted in note delete action test
            throw new InvalidOperationException('The main note cannot be deleted.');
        }

        if ($this->notePermissionVerifier->isGrantedToDelete($noteFromDb->userId)) {
            $deleted = $this->noteDeleterRepository->deleteNote($noteId);
            if ($deleted) {
                $this->userActivityLogger->logUserActivity(
                    UserActivity::DELETED,
                    'note',
                    $noteId,
                    ['message' => $noteFromDb->message]
                );
            }

            return $deleted;
        }
        throw new ForbiddenException('Not allowed to delete note.');
    }
}
