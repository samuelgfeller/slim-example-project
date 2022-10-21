<?php


namespace App\Domain\Note\Service;


use App\Domain\Exceptions\ForbiddenException;
use App\Domain\Factory\LoggerFactory;
use App\Domain\Note\Authorization\NoteAuthorizationChecker;
use App\Domain\Note\Data\NoteData;
use App\Infrastructure\Authentication\UserRoleFinderRepository;
use App\Infrastructure\Client\ClientUpdaterRepository;
use App\Infrastructure\Note\NoteCreatorRepository;
use Psr\Log\LoggerInterface;

class NoteCreator
{

    public function __construct(
        private readonly NoteValidator $noteValidator,
        private readonly NoteCreatorRepository $noteCreatorRepository,
        private readonly NoteAuthorizationChecker $noteAuthorizationChecker,
        LoggerFactory $logger
    ) {
    }

    /**
     * Note creation logic
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

        if ($this->noteAuthorizationChecker->isGrantedToCreate((int)$noteData['is_main'])){
            return $this->noteCreatorRepository->insertNote($note->toArray());
        }
        throw new ForbiddenException('Not allowed to create note.');
    }
}