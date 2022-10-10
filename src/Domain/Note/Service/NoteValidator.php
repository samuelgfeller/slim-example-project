<?php

namespace App\Domain\Note\Service;

use App\Domain\Exceptions\ValidationException;
use App\Domain\Factory\LoggerFactory;
use App\Domain\Note\Data\NoteData;
use App\Domain\Validation\AppValidation;
use App\Domain\Validation\ValidationResult;
use App\Infrastructure\Note\NoteValidatorRepository;
use App\Infrastructure\User\UserExistenceCheckerRepository;

/**
 * Class NoteValidator
 */
class NoteValidator extends AppValidation
{

    /**
     * NoteValidator constructor.
     *
     * @param LoggerFactory $logger
     * @param UserExistenceCheckerRepository $userExistenceCheckerRepository
     */
    public function __construct(
        LoggerFactory $logger,
        private readonly UserExistenceCheckerRepository $userExistenceCheckerRepository,
        private readonly NoteValidatorRepository $noteValidatorRepository,
    ) {
        parent::__construct(
            $logger->addFileHandler('error.log')->createInstance('note-validation')
        );
    }

    /**
     * Validate note creation
     *
     * @param NoteData $note
     * @throws ValidationException
     */
    public function validateNoteCreation(NoteData $note): void
    {
        // Exact validation error tested in NoteCaseProvider.php::provideNoteCreateInvalidData()
        $validationResult = new ValidationResult('There is something in the note data that couldn\'t be validated');

        $this->validateMessage($note->message, $validationResult, true);
        if ($note->isMain === 1) {
            $this->validateIsMainNote($note->clientId, $validationResult);
        }
        // It's a bit pointless to check user existence as user should always exist if he's logged in but here is how I'd do it
        $this->validateUser($note->userId, $validationResult, true);

        $this->throwOnError($validationResult);
    }

    /**
     * Validate note update
     *
     * @param NoteData $note
     * @throws ValidationException
     */
    public function validateNoteUpdate(NoteData $note): void
    {
        // Exact validation error tested in NoteCaseProvider.php::provideNoteCreateInvalidData()
        $validationResult = new ValidationResult('There is something in the note data that couldn\'t be validated');

        if (null !== $note->message) {
            $this->validateMessage($note->message, $validationResult, false);
        }

        $this->throwOnError($validationResult);
    }


    /**
     * Validate note message
     *
     * @param $noteMsg
     * @param ValidationResult $validationResult
     * @param bool $required
     * @return void
     */
    protected function validateMessage($noteMsg, ValidationResult $validationResult, bool $required): void
    {
        // Not test if empty string as user could submit note with empty string which has to be checked
        if (null !== $noteMsg) {
            $this->validateLengthMax($noteMsg, 'message', $validationResult, 500);
            $this->validateLengthMin($noteMsg, 'message', $validationResult, 4);
        } elseif (true === $required) {
            // If it is null or empty string and required
            $validationResult->setError('message', 'Message is required but not given');
        }
    }

    protected function validateUser($userId, ValidationResult $validationResult, bool $required): void
    {
        if (null !== $userId && '' !== $userId && $userId !== 0) {
            $this->validateUserExistence($userId, $validationResult);
        } elseif (true === $required) {
            // If it is null or empty string and required
            $validationResult->setError('user_id', 'user_id required but not given');
        }
    }

    /**
     * Validate that if if_main is true its valid
     *
     * @param int $clientId
     * @param ValidationResult $validationResult
     * @return void
     */
    protected function validateIsMainNote(int $clientId, ValidationResult $validationResult): void
    {
        $exists = $this->noteValidatorRepository->mainNoteAlreadyExistsForClient($clientId);
        if ($exists === true) {
            $validationResult->setError('is_main', 'Main note exists already');

            $this->logger->debug('Attempt to create main note but it already exists. Client: ' . $clientId);
        }
    }

    /**
     * Check if user exists
     * Same function than in UserValidator.
     *
     * @param $userId
     * @param ValidationResult $validationResult
     */
    protected function validateUserExistence($userId, ValidationResult $validationResult): void
    {
        $exists = $this->userExistenceCheckerRepository->userExists($userId);
        if (!$exists) {
            $validationResult->setError('user', 'User not existing');

            $this->logger->debug('Check for user (id: ' . $userId . ') that didn\'t exist in validation');
        }
    }

}
