<?php

namespace App\Module\Note\Validation\Service;

use App\Module\Note\Validation\Repository\NoteValidatorRepository;
use App\Module\Validation\Exception\ValidationException;
use Cake\Validation\Validator;
use Psr\Log\LoggerInterface;

final readonly class NoteValidator
{
    public function __construct(
        private NoteValidatorRepository $noteValidatorRepository,
        private LoggerInterface $logger,
    ) {
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
        $validator = $validator->requirePresence('message', $isCreateMode, __('Field is required'))
            ->maxLength('message', 1000, __('Maximum length is %d', 1000))
            // is_main and client_id keys only required on creation
            ->requirePresence('is_main', $isCreateMode, __('Field is required'))
            // When update or create request is called and all fields except is_main, something is wrong
            ->add('is_main', 'notEmptyOtherFields', [
                'rule' => function ($value, $context) {
                    return !(count($context['data']) === 1 && array_keys($context['data'])[0] === 'is_main');
                },
                'message' => __('Request body is empty'),
            ])
            ->requirePresence('client_id', $isCreateMode, __('Field is required'))
        ;

        // $noteValues could be an empty array if request body is empty
        if ((int)($noteValues['is_main'] ?? 0) === 1) {
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
                ->minLength('message', 4, __('Minimum length is %d', 4));
        }

        $errors = $validator->validate($noteValues);
        if ($errors) {
            throw new ValidationException($errors);
        }
    }
}
