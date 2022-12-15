<?php

namespace App\Domain\Note\Service;

use App\Domain\Factory\LoggerFactory;
use App\Domain\Note\Data\NoteData;
use App\Domain\Validation\ValidationException;
use App\Domain\Validation\ValidationResult;
use App\Domain\Validation\Validator;
use App\Infrastructure\Note\NoteValidatorRepository;
use Psr\Log\LoggerInterface;

/**
 * Class NoteValidator.
 */
class NoteValidator
{
    private LoggerInterface $logger;

    /**
     * NoteValidator constructor.
     *
     * @param NoteValidatorRepository $noteValidatorRepository
     * @param Validator $validator
     * @param LoggerFactory $loggerFactory
     */
    public function __construct(
        private readonly NoteValidatorRepository $noteValidatorRepository,
        private readonly Validator $validator,
        private readonly LoggerFactory $loggerFactory,
    ) {
        $this->logger = $this->loggerFactory->addFileHandler('error.log')->createInstance('note-validation');
    }

    /**
     * Validate note creation.
     *
     * @param NoteData $note
     *
     * @throws ValidationException
     */
    public function validateNoteCreation(NoteData $note): void
    {
        // Exact validation error tested in NoteCaseProvider.php::provideNoteCreateInvalidData()
        $validationResult = new ValidationResult('There is something in the note data that couldn\'t be validated');

        if ($note->isMain === 1) {
            $this->validateNoteMessage($note->message, $validationResult, false, 0);
            $this->validateMainNote($note->clientId, $validationResult);
        } else {
            $this->validateNoteMessage($note->message, $validationResult, false);
        }
        // It's a bit pointless to check user existence as user should always exist if he's logged in but here is how I'd do it
        $this->validator->validateExistence($note->userId, 'user', $validationResult, true);

        $this->validator->throwOnError($validationResult);
    }

    /**
     * Validate note update.
     *
     * @param NoteData $note
     *
     * @throws ValidationException
     */
    public function validateNoteUpdate(NoteData $note): void
    {
        // Exact validation error tested in NoteCaseProvider.php::provideNoteCreateInvalidData()
        $validationResult = new ValidationResult('There is something in the note data that couldn\'t be validated');

        if (null !== $note->message) {
            if ($note->isMain === 1) {
                // If main note, min length is 0 as it's not possible to delete it
                $this->validateNoteMessage($note->message, $validationResult, false, 0);
            } else {
                $this->validateNoteMessage($note->message, $validationResult, false);
            }
        }
        if (null !== $note->hidden) {
            // Has to be either 0 or 1
            $this->validator->validateNumeric($note->hidden, 'hidden', $validationResult);
        }

        $this->validator->throwOnError($validationResult);
    }

    /**
     * Validate note message.
     *
     * @param $noteMsg
     * @param ValidationResult $validationResult
     * @param bool $required
     * @param int $minLength the min length for a normal note is 4
     * but as main note cannot be deleted, it has to be 0 for the main note
     *
     * @return void
     */
    protected function validateNoteMessage(
        $noteMsg,
        ValidationResult $validationResult,
        bool $required,
        int $minLength = 4
    ): void {
        // Not test if empty string as user could submit note with empty string which has to be checked
        if (null !== $noteMsg) {
            $this->validator->validateLengthMax($noteMsg, 'message', $validationResult, 1000);
            $this->validator->validateLengthMin($noteMsg, 'message', $validationResult, $minLength);
        } elseif (true === $required) {
            // If it is null or empty string and required
            $validationResult->setError('message', 'Message is required.');
        }
    }

    /**
     * Validate if main note can be created.
     *
     * @param int $clientId
     * @param ValidationResult $validationResult
     *
     * @return void
     */
    protected function validateMainNote(int $clientId, ValidationResult $validationResult): void
    {
        $exists = $this->noteValidatorRepository->mainNoteAlreadyExistsForClient($clientId);
        if ($exists === true) {
            $validationResult->setError('is_main', 'Main note exists already');

            $this->logger->debug('Attempt to create main note but it already exists. Client: ' . $clientId);
        }
    }
}
