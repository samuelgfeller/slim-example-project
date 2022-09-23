<?php


namespace App\Domain\Note\Service;


use App\Domain\Exceptions\ForbiddenException;
use App\Domain\Factory\LoggerFactory;
use App\Domain\Note\Data\NoteData;
use App\Infrastructure\Authentication\UserRoleFinderRepository;
use App\Infrastructure\Client\ClientUpdaterRepository;
use App\Infrastructure\Note\NoteCreatorRepository;
use Psr\Log\LoggerInterface;

class NoteCreator
{

    private LoggerInterface $logger;

    public function __construct(
        private readonly NoteValidator $noteValidator,
        private readonly NoteCreatorRepository $noteCreatorRepository,
        private readonly UserRoleFinderRepository $userRoleFinderRepository,
        private readonly ClientUpdaterRepository $clientUpdaterRepository,
        LoggerFactory $logger
    ) {
        $this->logger = $logger->addFileHandler('error.log')->createInstance('note-service');
    }

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

        // If it's a new main note, create and add it to the client
        $userRole = $this->userRoleFinderRepository->getUserRoleById($loggedInUserId);
        if ($noteData['is_main'] === 1 || $noteData['is_main'] === '1') {
            // Check user rights but for now everybody that is logged in can create main note
            if ($userRole === 'admin' || $userRole === 'user') {
                return $this->noteCreatorRepository->insertNote($note->toArray());
            }
            // User does not have needed rights to access area or function
            $this->logger->notice(
                'User ' . $loggedInUserId . ' tried to create main note with client id: ' . $note->clientId
            );
            throw new ForbiddenException('Not allowed to change that note.');
        }

        // Not main note, just create normal note
        return $this->noteCreatorRepository->insertNote($note->toArray());
    }
}