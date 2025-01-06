<?php

namespace App\Module\Client\Domain\Service;

use App\Module\Client\Enum\ClientVigilanceLevel;
use App\Module\Client\Repository\ClientStatus\ClientStatusFinderRepository;
use App\Module\Exception\Domain\ValidationException;
use App\Module\User\Repository\UserFinderRepository;
use Cake\Validation\Validator;

final readonly class ClientValidator
{
    public function __construct(
        private ClientStatusFinderRepository $clientStatusFinderRepository,
        private UserFinderRepository $userFinderRepository,
    ) {
    }

    /**
     * Validate client creation and modification.
     *
     * @param array $clientCreationValues user submitted values
     * @param bool $isCreateMode true when fields presence is required in values. For update, they're optional
     *
     * @throws ValidationException
     */
    public function validateClientValues(array $clientCreationValues, bool $isCreateMode = true): void
    {
        $validator = new Validator();

        $validator
            // Require presence indicates that the Field is required in the request body
            // When second parameter "mode" is false, the field presence is not required
            ->requirePresence('first_name', $isCreateMode, __('Field is required'))
            // allowEmptyString to enable null and empty string because cakephp validation library automatically
            // sets a rule that the field cannot be null when there is another validation rule set for the field.
            ->allowEmptyString('first_name')
            ->minLength('first_name', 2, __('Minimum length is %d', 2))
            ->maxLength('first_name', 100, __('Maximum length is %d', 100))
            ->requirePresence('last_name', $isCreateMode, __('Field is required'))
            ->allowEmptyString('last_name')
            ->minLength('last_name', 2, __('Minimum length is %d', 2))
            ->maxLength('last_name', 100, __('Maximum length is %d', 100))
            ->requirePresence('email', $isCreateMode, __('Field is required'))
            ->allowEmptyString('email')
            ->email('email', false, __('Invalid email'))
            ->requirePresence('birthdate', $isCreateMode, __('Field is required'))
            ->allowEmptyDate('birthdate')
            ->add('birthdate', [
                'date' => [
                    'rule' => ['date', ['ymd', 'mdy', 'dmy']],
                    'message' => __('Invalid date value'),
                    // This rule must succeed before proceeding to the next one
                    // Date must be valid as DateTime objects are created in the next rules
                    'last' => true,
                ],
                'validateNotInFuture' => [
                    'rule' => function ($value, $context) {
                        $today = new \DateTime();
                        $birthdate = new \DateTime($value);

                        // Check that birthdate is not in the future
                        return $birthdate <= $today;
                    },
                    'message' => __('Cannot be in the future'),
                ],
                'validateOldestAge' => [
                    'rule' => function ($value, $context) {
                        $birthdate = new \DateTime($value);
                        // check that birthdate is not older than 130 years
                        $oldestBirthdate = new \DateTime('-130 years');

                        return $birthdate >= $oldestBirthdate;
                    },
                    'message' => __('Cannot be older than 130 years'),
                ],
            ])
            ->requirePresence('location', $isCreateMode, __('Field is required'))
            ->allowEmptyString('location')
            ->minLength('location', 2, __('Minimum length is %d', 2))
            ->maxLength('location', 100, __('Maximum length is %d', 100))
            ->requirePresence('phone', $isCreateMode, __('Field is required'))
            ->allowEmptyString('phone')
            ->minLength('phone', 3, __('Minimum length is %d', 3))
            ->maxLength('phone', 20, __('Maximum length is %d', 20))
            // Sex requirePresence false as it's not required and the browser won't send the key over if not set
            ->requirePresence('sex', false)
            ->allowEmptyString('sex')
            ->inList('sex', ['M', 'F', 'O', ''], 'Invalid option')
            // This is too complex and will have to be changed in the future. Enums are not ideal in general.
            // requirePresence false for vigilance_level as it doesn't even exist on the create form, only possible to update
            ->requirePresence('vigilance_level', false)
            ->allowEmptyString('vigilance_level')
             ->add('vigilance_level', 'validateBackedEnum', [
                 'rule' => function ($value, $context) {
                     return in_array($value, ClientVigilanceLevel::values(), true);
                 },
                 'message' => __('Invalid option'),
             ])
            // Client message presence is not required as it's only set if user submits the form via api
            ->requirePresence('client_message', false)
            ->allowEmptyString('client_message')
            ->minLength('client_message', 3, __('Minimum length is %d', 3))
            ->maxLength('client_message', 1000, __('Maximum length is %d', 1000))
            ->requirePresence('client_status_id', $isCreateMode, __('Field is required'))
            ->notEmptyString('client_status_id', __('Required'))
            ->numeric('client_status_id', __('Invalid option format'))
            ->add('client_status_id', 'exists', [
                'rule' => function ($value, $context) {
                    return $this->clientStatusFinderRepository->clientStatusExists((int)$value);
                },
                'message' => __('Invalid option'),
            ])
            // Presence not required as client can be created via form submit by another frontend that doesn't have user id
            ->requirePresence('user_id', false)
            ->allowEmptyString('user_id', __('Required'))
            ->numeric('user_id', __('Invalid option format'))
            ->add('user_id', 'exists', [
                'rule' => function ($value, $context) {
                    return !empty($this->userFinderRepository->findUserById((int)$value));
                },
                'message' => __('Invalid option'),
            ])
        ;

        $errors = $validator->validate($clientCreationValues);
        if ($errors) {
            throw new ValidationException($errors);
        }
    }
}
