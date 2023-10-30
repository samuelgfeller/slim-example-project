<?php

namespace App\Domain\Note\Service;

use App\Domain\Factory\LoggerFactory;
use App\Domain\Validation\ValidationException;
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
     * @param LoggerFactory $loggerFactory
     */
    public function __construct(
        private readonly NoteValidatorRepository $noteValidatorRepository,
        private readonly LoggerFactory $loggerFactory,
    ) {
        $this->logger = $this->loggerFactory->addFileHandler('error.log')->createLogger('note-validation');
    }

    /**
     * Validate note creation and modification.
     *
     * @param array $noteValues
     * @param bool $isCreateMode true if note is being created, false when updated
     */
    public function validateNoteValues(array $noteValues, bool $isCreateMode = true): void
    {
        $validator = new Validator();
        $validator = $validator->requirePresence('message', true, __('Key is required'))
            ->maxLength('message', 1000, __('Maximum length is 1000', 1000))
            // is_main and client_id keys only required on creation
            ->requirePresence('is_main', $isCreateMode, __('Key is required'))
            ->requirePresence('client_id', $isCreateMode, __('Key is required'));

        if ((int)$noteValues['is_main'] === 1) {
            // If main note, the min length can be 0 as we can't delete it
            $validator->allowEmptyString('message');
            // Validate that main note doesn't already exist for client on creation
            if ($isCreateMode) {
                $validator
                    ->add('is_main', 'mainNoteAlreadyExists', [
                        'rule' => function ($value, $context) {
                            $clientId = $context['data']['client_id'];
                            // Log error as this should not be possible if frontend behaves correctly
                            $this->logger->error(
                                'Attempt to create main note but it already exists. Client: ' . $clientId
                            );

                            return $this->noteValidatorRepository->mainNoteAlreadyExistsForClient($clientId) === false;
                        },
                        'message' => __('Main note already exists'),
                    ]);
            }
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
}
