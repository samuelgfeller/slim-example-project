<?php


namespace App\Domain\Note\Service;


use App\Domain\Client\Exception\NotAllowedException;
use App\Domain\Exceptions\ForbiddenException;
use App\Domain\Note\Authorization\NoteAuthorizationChecker;
use App\Infrastructure\Note\NoteDeleterRepository;

class NoteDeleter
{
    public function __construct(
        private readonly NoteDeleterRepository $noteDeleterRepository,
        private readonly NoteFinder $noteFinder,
        private readonly NoteAuthorizationChecker $noteAuthorizationChecker,
    ) { }

    /**
     * Delete one note logic
     *
     * @param int $noteId
     * @return bool
     */
    public function deleteNote(int $noteId): bool
    {
        // Find note in db to get its ownership
        $noteFromDb = $this->noteFinder->findNote($noteId);

        // There is no option in GUI to delete main note
        if ($noteFromDb->isMain === 1){
            // Asserted in testClientReadNoteDeletion
            throw new NotAllowedException('The main note cannot be deleted.');
        }

        if ($this->noteAuthorizationChecker->isGrantedToDelete($noteFromDb->userId)){
            return $this->noteDeleterRepository->deleteNote($noteId);
        }
        throw new ForbiddenException('You have to be admin or the note creator to update this note');
    }
}