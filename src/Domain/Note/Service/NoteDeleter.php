<?php


namespace App\Domain\Note\Service;


use App\Domain\Client\Exception\NotAllowedException;
use App\Domain\Exceptions\ForbiddenException;
use App\Infrastructure\Authentication\UserRoleFinderRepository;
use App\Infrastructure\Note\NoteDeleterRepository;

class NoteDeleter
{
    public function __construct(
        private readonly NoteDeleterRepository $noteDeleterRepository,
        private readonly NoteFinder $noteFinder,
        private readonly UserRoleFinderRepository $userRoleFinderRepository,
    ) { }

    /**
     * Delete one note logic
     *
     * @param int $noteId
     * @param int $loggedInUserId
     * @return bool
     */
    public function deleteNote(int $noteId, int $loggedInUserId): bool
    {
        // Find note in db to get its ownership
        $noteFromDb = $this->noteFinder->findNote($noteId);

        // There is no option in GUI to delete main note
        if ($noteFromDb->isMain === 1){
            // Asserted in testClientReadNoteDeletion
            throw new NotAllowedException('The main note cannot be deleted.');
        }

        $userRole = $this->userRoleFinderRepository->getUserRoleById($loggedInUserId);

        // Check if it's admin or if it's its own note
        if ($userRole === 'admin' || $noteFromDb->userId === $loggedInUserId) {
            return $this->noteDeleterRepository->deleteNote($noteId);
        }
        throw new ForbiddenException('You have to be admin or the note creator to update this note');
    }
}