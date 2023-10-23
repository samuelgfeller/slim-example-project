<?php

namespace App\Domain\Note\Service;

use App\Domain\Factory\LoggerFactory;
use App\Domain\Note\Data\NoteData;
use App\Domain\Validation\ValidationException;
use App\Domain\Validation\ValidationExceptionOld;
use App\Domain\Validation\ValidationResult;
use App\Domain\Validation\ValidatorNative;
use App\Infrastructure\Note\NoteValidatorRepository;
use Cake\Validation\Validator;
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
     * @param ValidatorNative $validatorNative
     * @param LoggerFactory $loggerFactory
     */
    public function __construct(
        private readonly NoteValidatorRepository $noteValidatorRepository,
        private readonly ValidatorNative $validatorNative,
        private readonly LoggerFactory $loggerFactory,
    ) {
        $this->logger = $this->loggerFactory->addFileHandler('error.log')->createLogger('note-validation');
    }

    /**
     * Validate note creation.
     *
     * @param array $noteValues
     */
    public function validateNoteCreation(array $noteValues): void
    {
        $validator = new Validator();
        $validator = $validator->requirePresence('message')
            ->maxLength('message', 1000, __('Maximum length is 1000', 1000));

        if ((int)$noteValues['is_main'] === 1) {
            // If main note, the min length can be 0 as we can't delete it
            $validator
                ->add('is_main', 'mainNoteAlreadyExists', [
                    'rule' => function ($value, $context) {
                        $clientId = $context['data']['client_id'];
                        // Log error as this should not be possible if frontend behaves correctly
                        $this->logger->error('Attempt to create main note but it already exists. Client: ' . $clientId);
                        return $this->noteValidatorRepository->mainNoteAlreadyExistsForClient($clientId) === false;
                    },
                    'message' => __('Main note already exists'),
                ]);
        } else {
            // If not main note, min length is 4
            $validator
                ->minLength('message', 4, __('Minimum length is 4', 4));
        }

        $errors = $validator->validate($noteValues);
        if ($errors) {
            throw new ValidationException($errors);
        }
    }

    public function validateNoteCreationOld(NoteData $note): void
    {
        // Validation error message asserted via NoteProvider.php
        $validationResult = new ValidationResult('There is something in the note data that couldn\'t be validated');

        if ($note->isMain === 1) {
            $this->validateNoteMessage($note->message, $validationResult, false, 0);
            $this->validateMainNote($note->clientId, $validationResult);
        } else {
            $this->validateNoteMessage($note->message, $validationResult, false);
        }
        // It's a bit pointless to check user existence as user should always exist if he's logged in but here is how I'd do it
        $this->validatorNative->validateExistence($note->userId, 'user', $validationResult, true);

        $this->validatorNative->throwOnError($validationResult);
    }

    /**
     * Validate note update.
     *
     * @param NoteData $note
     *
     * @throws ValidationExceptionOld
     */
    public function validateNoteUpdate(NoteData $note): void
    {
        // Validation error message asserted via NoteProvider.php
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
            $this->validatorNative->validateNumeric($note->hidden, 'hidden', $validationResult);
        }

        $this->validatorNative->throwOnError($validationResult);
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
            $this->validatorNative->validateLengthMax($noteMsg, 'message', $validationResult, 1000);
            $this->validatorNative->validateLengthMin($noteMsg, 'message', $validationResult, $minLength);
        } elseif (true === $required) {
            // If it is null or empty string and required
            $validationResult->setError('message', __('Required'));
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
            // Log error as this should not be possible if frontend behaves correctly
            $this->logger->error('Attempt to create main note but it already exists. Client: ' . $clientId);
        }
    }
}
