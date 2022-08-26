<?php

namespace App\Domain\Note\Service;

use App\Domain\Exceptions\ValidationException;
use App\Domain\Factory\LoggerFactory;
use App\Domain\Note\Data\NoteData;
use App\Domain\Validation\AppValidation;
use App\Domain\Validation\ValidationResult;
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
        private UserExistenceCheckerRepository $userExistenceCheckerRepository
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
        // It's a bit pointless to check user existence as user should always exist if he's logged in
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


    protected function validateMessage($noteMsg, ValidationResult $validationResult, bool $required): void
    {
        if (null !== $noteMsg && '' !== $noteMsg) {
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
            $validationResult->setMessage('User not found');
            $validationResult->setError('user', 'User not existing');

            $this->logger->debug('Check for user (id: ' . $userId . ') that didn\'t exist in validation');
        }
    }

}
