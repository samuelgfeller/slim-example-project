<?php


namespace App\Domain\Note\Service;


use App\Domain\Exceptions\ForbiddenException;
use App\Infrastructure\Authentication\UserRoleFinderRepository;
use App\Infrastructure\Note\NoteDeleterRepository;

class NoteDeleter
{
    public function __construct(
        private NoteDeleterRepository $noteDeleterRepository,
        private NoteFinder $noteFinder,
        private UserRoleFinderRepository $userRoleFinderRepository,
    ) { }

    /**
     * Delete one note logic
     *
     * @param int $noteId
     * @param int $loggedInUserId
     * @return bool
     * @throws ForbiddenException
     */
    public function deleteNote(int $noteId, int $loggedInUserId): bool
    {
        // Find note in db to get its ownership
        $noteFromDb = $this->noteFinder->findNote($noteId);

        $userRole = $this->userRoleFinderRepository->getUserRoleById($loggedInUserId);

        // Check if it's admin or if it's its own note
        if ($userRole === 'admin' || $noteFromDb->userId === $loggedInUserId) {
            return $this->noteDeleterRepository->deleteNote($noteId);
        }
        throw new ForbiddenException('You have to be admin or the note creator to update this note');
    }
}