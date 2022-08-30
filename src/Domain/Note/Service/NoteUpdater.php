<?php


namespace App\Domain\Note\Service;


use App\Domain\Exceptions\ForbiddenException;
use App\Domain\Factory\LoggerFactory;
use App\Domain\Note\Data\NoteData;
use App\Infrastructure\Authentication\UserRoleFinderRepository;
use App\Infrastructure\Exceptions\PersistenceRecordNotFoundException;
use App\Infrastructure\Note\NoteUpdaterRepository;
use Psr\Log\LoggerInterface;

class NoteUpdater
{
    private LoggerInterface $logger;

    public function __construct(
        private NoteValidator $noteValidator,
        private NoteUpdaterRepository $noteUpdaterRepository,
        private UserRoleFinderRepository $userRoleFinderRepository,
        private NoteFinder $noteFinder,
        LoggerFactory $logger

    ) {
        $this->logger = $logger->addFileHandler('error.log')->createInstance('note-service');
    }

    /**
     * Change something or multiple things on note
     *
     * @param int $noteId id of note being changed
     * @param array|null $noteValues values that have to be changed
     * @param int $loggedInUserId
     * @return bool if update was successful
     */
    public function updateNote(int $noteId, null|array $noteValues, int $loggedInUserId): bool
    {
        // Init object for validation
        $note = new NoteData($noteValues);
        // Validate object
        $this->noteValidator->validateNoteUpdate($note);

        // Find note in db to compare its ownership
        $noteFromDb = $this->noteFinder->findNote($noteId);
//sleep(1);
        // I write the role logic always for each function and not a general service "isAuthorised" function because it's too different every time
        $userRole = $this->userRoleFinderRepository->getUserRoleById($loggedInUserId);
        // Check if it's admin or if it's its own note
        if ($userRole === 'admin' || $noteFromDb->userId === $loggedInUserId) {
            // The only thing that a user can change on a note is its message
            if (null !== $note->message) {
                // To be sure that only the message will be updated (not using toArray from object)
                $updateData['message'] = $note->message;
                return $this->noteUpdaterRepository->updateNote($updateData, $noteId);
            }
            // Nothing was updated as message was empty
            return false;
        }
        // User does not have needed rights to access area or function
        $this->logger->notice(
            'User ' . $loggedInUserId . ' tried to update other note with id: ' . $loggedInUserId
        );
        throw new ForbiddenException('Not allowed to change that note.');
    }
}