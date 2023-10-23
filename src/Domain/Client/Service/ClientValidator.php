<?php

namespace App\Domain\Client\Service;

use App\Domain\Client\Enum\ClientVigilanceLevel;
use App\Domain\Factory\LoggerFactory;
use App\Domain\Validation\ValidationException;
use App\Domain\Validation\ValidationResult;
use App\Domain\Validation\ValidatorNative;
use App\Infrastructure\Client\ClientStatus\ClientStatusFinderRepository;
use App\Infrastructure\User\UserFinderRepository;
use Cake\Validation\Validator;

class ClientValidator
{
    public function __construct(
        LoggerFactory $logger,
        private readonly ValidatorNative $validator,
        private readonly ClientStatusFinderRepository $clientStatusFinderRepository,
        private readonly UserFinderRepository $userFinderRepository,
    ) {
        $logger->addFileHandler('error.log')->createLogger('post-validation');
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
            // Require presence indicates that the key is required in the request body
            // When second parameter "mode" is false, the fields presence is not required
            ->requirePresence('first_name', $isCreateMode, __('Key is required'))
            // Cake validation library automatically sets a rule that field cannot be null as soon as there is any
            // validation rule set for the field. This is why we have to allow empty string (null is also allowed by it)
            ->allowEmptyString('first_name')
            ->minLength('first_name', 2, __('Minimum length is 2'))
            ->maxLength('first_name', 100, __('Maximum length is 100'))
            ->requirePresence('last_name', $isCreateMode, __('Key is required'))
            ->allowEmptyString('last_name')
            ->minLength('last_name', 2, __('Minimum length is 2'))
            ->maxLength('last_name', 100, __('Maximum length is 100'))
            ->requirePresence('email', $isCreateMode, __('Key is required'))
            ->allowEmptyString('email')
            ->email('email', false, __('Invalid email'))
            ->requirePresence('birthdate', $isCreateMode, __('Key is required'))
            ->allowEmptyDate('birthdate')
            ->date('birthdate', ['ymd', 'mdy', 'dmy'], __('Invalid date value'))
            ->add('birthdate', 'validateNotInFuture', [
                'rule' => function ($value, $context) {
                    $today = new \DateTime();
                    $birthdate = new \DateTime($value);
                    // check that birthdate is not in the future
                    return $birthdate <= $today;
                },
                'message' => __('Cannot be in the future')
            ])
            ->add('birthdate', 'validateOldestAge', [
                'rule' => function ($value, $context) {
                    $birthdate = new \DateTime($value);
                    // check that birthdate is not older than 130 years
                    $oldestBirthdate = new \DateTime('-130 years');
                    return $birthdate >= $oldestBirthdate;
                },
                'message' => __('Cannot be older than 130 years')
            ])
            ->requirePresence('location', $isCreateMode, __('Key is required'))
            ->allowEmptyString('location')
            ->minLength('location', 2, __('Minimum length is 2'))
            ->maxLength('location', 100, __('Maximum length is 100'))
            ->requirePresence('phone', $isCreateMode, __('Key is required'))
            ->allowEmptyString('phone')
            ->minLength('phone', 3, __('Minimum length is 3'))
            ->maxLength('phone', 20, __('Maximum length is 20'))
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
                    return $this->isBackedEnum($value, ClientVigilanceLevel::class);
                },
                'message' => __('Invalid option')
            ])
            // Client message presence is not required as it's only set if user submits the form via api
            ->requirePresence('client_message', false)
            ->allowEmptyString('client_message')
            ->minLength('client_message', 3, __('Minimum length is 3'))
            ->maxLength('client_message', 1000, __('Maximum length is 1000'))
            ->requirePresence('client_status_id', $isCreateMode, __('Key is required'))
            ->notEmptyString('client_status_id', __('Required'))
            ->numeric('client_status_id', __('Invalid option format'))
            ->add('client_status_id', 'exists', [
                'rule' => function ($value, $context) {
                    return $this->clientStatusFinderRepository->clientStatusExists((int)$value);
                },
                'message' => __('Invalid option')
            ])
            // Presence not required as client can be created via form submit by another frontend that doesn't have user id
            ->requirePresence('user_id', false)
            ->allowEmptyString('user_id', __('Required'))
            ->numeric('user_id', __('Invalid option format'))
            ->add('user_id', 'exists', [
                'rule' => function ($value, $context) {
                    return !empty($this->userFinderRepository->findUserById((int)$value));
                },
                'message' => __('Invalid option')
            ])
        ;

        $errors = $validator->validate($clientCreationValues);
        if ($errors) {
            throw new ValidationException($errors);
        }
    }

    /**
     * Check if value is a specific backed enum case or string
     * that can be converted into one.
     *
     * @param \BackedEnum|string|null $value
     * @param string $enum
     * @return bool
     */
    public function isBackedEnum(\BackedEnum|string|null $value, string $enum): bool
    {
        // If $value is already an enum case, it means that its valid otherwise try to convert it to enum case
        return is_a($value, $enum, true) || is_a($enum::tryFrom($value), $enum, true);
    }

    /**
     * Validate post update.
     *
     * @param array $clientValues values that user wants to change
     */
    public function validateClientUpdate(array $clientValues): void
    {
        // Validation error message asserted in ClientUpdateActionTest
        $validationResult = new ValidationResult('There is something in the client data that couldn\'t be validated');

        // Using array_key_exists instead of isset as isset returns false if value is null and key exists
        if (array_key_exists('client_status_id', $clientValues)) {
            $this->validateClientStatusId($clientValues['client_status_id'], $validationResult, false);
        }
        if (array_key_exists('user_id', $clientValues)) {
            $this->validateUserId($clientValues['user_id'], $validationResult, false);
        }
        if (array_key_exists('first_name', $clientValues)) {
            $this->validator->validateName($clientValues['first_name'], 'first_name', $validationResult, false);
        }
        if (array_key_exists('last_name', $clientValues)) {
            $this->validator->validateName($clientValues['last_name'], 'last_name', $validationResult, false);
        }
        if (array_key_exists('email', $clientValues)) {
            $this->validator->validateEmail($clientValues['email'], $validationResult, false);
        }
        if (array_key_exists('birthdate', $clientValues)) {
            $this->validator->validateBirthdate($clientValues['birthdate'], $validationResult, false);
        }
        if (array_key_exists('location', $clientValues)) {
            $this->validateLocation($clientValues['location'], $validationResult, false);
        }
        if (array_key_exists('phone', $clientValues)) {
            $this->validatePhone($clientValues['phone'], $validationResult, false);
        }
        if (array_key_exists('sex', $clientValues)) {
            $this->validateSex($clientValues['sex'], $validationResult, false);
        }
        if (array_key_exists('vigilance_level', $clientValues)) {
            $this->validator->validateBackedEnum(
                $clientValues['vigilance_level'],
                ClientVigilanceLevel::class,
                'vigilance_level',
                $validationResult,
                false
            );
        }

        $this->validator->throwOnError($validationResult);
    }

    // Validate functions for each field

    /**
     * Validate client user id dropdown.
     *
     * @param $value
     * @param ValidationResult $validationResult
     * @param bool $required
     *
     * @return void
     */
    protected function validateUserId($value, ValidationResult $validationResult, bool $required = false): void
    {
        if (null !== $value && '' !== $value) {
            $this->validator->validateNumeric($value, 'user_id', $validationResult, $required);
            $this->validator->validateExistence((int)$value, 'user', $validationResult, $required);
        } elseif (true === $required) {
            // If it is null or empty string and required
            $validationResult->setError('user_id', __('Required'));
        }
    }

    /**
     * Validate client status dropdown.
     *
     * @param mixed $value
     * @param ValidationResult $validationResult
     * @param bool $required
     *
     * @return void
     */
    protected function validateClientStatusId(
        mixed $value,
        ValidationResult $validationResult,
        bool $required = false
    ): void {
        if (null !== $value && '' !== $value) {
            $this->validator->validateNumeric($value, 'client_status_id', $validationResult, $required);
            $this->validator->validateExistence((int)$value, 'client_status', $validationResult, $required);
        } elseif (true === $required) {
            // If it is null or empty string and required
            $validationResult->setError('client_status_id', __('Required'));
        }
    }

    /**
     * Validate client location input.
     *
     * @param $location
     * @param ValidationResult $validationResult
     * @param bool $required
     *
     * @return void
     */
    protected function validateLocation($location, ValidationResult $validationResult, bool $required = false): void
    {
        if (null !== $location && '' !== $location) {
            $this->validator->validateLengthMax($location, 'location', $validationResult, 100);
            $this->validator->validateLengthMin($location, 'location', $validationResult, 2);
        } elseif (true === $required) {
            // If it is null or empty string and required
            $validationResult->setError('location', __('Required'));
        }
    }

    /**
     * Validate client phone input.
     *
     * @param $value
     * @param ValidationResult $validationResult
     * @param bool $required
     *
     * @return void
     */
    protected function validatePhone($value, ValidationResult $validationResult, bool $required = false): void
    {
        if (null !== $value && '' !== $value) {
            $this->validator->validateLengthMax($value, 'phone', $validationResult, 20);
            $this->validator->validateLengthMin($value, 'phone', $validationResult, 3);
        } elseif (true === $required) {
            // If it is null or empty string and required
            $validationResult->setError('phone', __('Required'));
        }
    }

    /**
     * Validate client sex options.
     *
     * @param $value
     * @param ValidationResult $validationResult
     * @param bool $required
     *
     * @return void
     */
    protected function validateSex($value, ValidationResult $validationResult, bool $required = false): void
    {
        if (null !== $value && '' !== $value) {
            if (!in_array($value, ['M', 'F', 'O', ''], true)) {
                $validationResult->setError('sex', __('Invalid option'));
            }
        } elseif (true === $required) {
            // If it is null or empty string and required
            $validationResult->setError('sex', __('Required'));
        }
    }

    /**
     * Validate client message input.
     *
     * @param $value
     * @param ValidationResult $validationResult
     * @param bool $required
     *
     * @return void
     */
    private function validateClientMessage($value, ValidationResult $validationResult, bool $required = false): void
    {
        if (null !== $value && '' !== $value) {
            $this->validator->validateLengthMax($value, 'client_message', $validationResult, 1000);
            $this->validator->validateLengthMin($value, 'client_message', $validationResult, 3);
        } elseif (true === $required) {
            // If it is null or empty string and required
            $validationResult->setError('client_message', __('Required'));
        }
    }
}
