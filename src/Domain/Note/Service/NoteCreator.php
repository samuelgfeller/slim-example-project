<?php


namespace App\Domain\Note\Service;


use App\Domain\Exceptions\ForbiddenException;
use App\Domain\Factory\LoggerFactory;
use App\Domain\Note\Authorization\NoteAuthorizationChecker;
use App\Domain\Note\Data\NoteData;
use App\Infrastructure\Note\NoteCreatorRepository;
use Odan\Session\SessionInterface;

class NoteCreator
{

    public function __construct(
        private readonly NoteValidator $noteValidator,
        private readonly NoteCreatorRepository $noteCreatorRepository,
        private readonly NoteAuthorizationChecker $noteAuthorizationChecker,
        private readonly SessionInterface $session,
        LoggerFactory $logger
    ) {
    }

    /**
     * Note creation logic
     *
     * @param array $noteValues
     *
     * @return int insert id
     */
    public function createNote(array $noteValues): int
    {
        if (($loggedInUserId = $this->session->get('user_id')) !== null) {
            $note = new NoteData($noteValues);
            $note->userId = $loggedInUserId;
            $this->noteValidator->validateNoteCreation($note);

            if ($this->noteAuthorizationChecker->isGrantedToCreate((int)$noteValues['is_main'])) {
                return $this->noteCreatorRepository->insertNote($note->toArray());
            }
        }
        throw new ForbiddenException('Not allowed to create note.');
    }
}