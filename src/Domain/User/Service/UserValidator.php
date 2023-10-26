<?php

namespace App\Domain\User\Service;

use App\Domain\User\Enum\UserLang;
use App\Domain\User\Enum\UserStatus;
use App\Domain\User\Enum\UserTheme;
use App\Domain\Validation\ValidationException;
use App\Domain\Validation\ValidationExceptionOld;
use App\Domain\Validation\ValidationResult;
use App\Domain\Validation\ValidatorNative;
use App\Infrastructure\Authentication\UserRoleFinderRepository;
use App\Infrastructure\User\UserFinderRepository;
use Cake\Validation\Validator;

/**
 * Class UserValidator.
 */
class UserValidator
{
    public function __construct(
        private readonly ValidatorNative $validatorNative,
        private readonly UserFinderRepository $userFinderRepository,
        private readonly UserRoleFinderRepository $userRoleFinderRepository,
    ) {
    }

    /**
     * Validate updating the user.
     *
     * @param int $userId
     * @param array $userValues values to change
     *
     * @return ValidationResult
     */
    public function validateUserUpdate(int $userId, array $userValues): ValidationResult
    {
        $validationResult = new ValidationResult('There is a validation error when trying to update a user');
        // Check that user exists
        $this->validatorNative->validateExistence($userId, 'user', $validationResult, true);

        // Using array_key_exists instead of isset as isset returns false if value is null and key exists
        if (array_key_exists('first_name', $userValues)) {
            $this->validatorNative->validateName($userValues['first_name'], 'first_name', $validationResult, true);
        }
        if (array_key_exists('surname', $userValues)) {
            $this->validatorNative->validateName($userValues['surname'], 'surname', $validationResult, true);
        }
        if (array_key_exists('email', $userValues)) {
            if ($this->validatorNative->resourceExistenceCheckerRepository->rowExists(
                ['email' => $userValues['email'], 'id !=' => $userId],
                'user'
            )) {
                $validationResult->setError('email', __('Email already exists'));
            }
            $this->validatorNative->validateEmail($userValues['email'], $validationResult, true);
        }
        if (array_key_exists('status', $userValues)) {
            $this->validateUserStatus($userValues['status'], $validationResult, true);
        }
        if (array_key_exists('theme', $userValues)) {
            $this->validatorNative->validateBackedEnum(
                $userValues['theme'],
                UserTheme::class,
                'theme',
                $validationResult
            );
        }
        if (array_key_exists('language', $userValues)) {
            $this->validatorNative->validateBackedEnum(
                $userValues['language'],
                UserLang::class,
                'language',
                $validationResult
            );
        }

        if (array_key_exists('user_role_id', $userValues)) {
            $this->validateUserRoleId($userValues['user_role_id'], $validationResult, true);
        }

        // If the validation failed, throw the exception that will be caught in the Controller
        $this->validatorNative->throwOnError($validationResult);

        return $validationResult;
    }

    /**
     * Validate user registration and update.
     *
     * @param $userValues
     * @param bool $isCreateMode
     */
    public function validateUserValues($userValues, bool $isCreateMode = true): void
    {
        $validator = new Validator();

        // Cake validation library automatically sets a rule that field cannot be null as soon as there is any
        // validation rule set for the field. This is why we have to allowEmptyString because it also allows null.
        // But for the User there are no optional fields meaning that if any field is passed, it has to contain a value.

        $validator
            // First name and surname are required to have values if they're given so no allowEmptyString
            ->requirePresence('first_name', $isCreateMode, __('Key is required'))
            ->minLength('first_name', 2, __('Minimum length is 2'))
            ->maxLength('first_name', 100, __('Maximum length is 100'))
            ->requirePresence('surname', $isCreateMode, __('Key is required'))
            ->minLength('surname', 2, __('Minimum length is 2'))
            ->maxLength('surname', 100, __('Maximum length is 100'))
            ->requirePresence('email', $isCreateMode, __('Key is required'))
            ->email('email', false, __('Invalid email'))
            ->add('email', 'emailIsUnique', [
                'rule' => function ($value, $context) {
                    // Check if email already exists. On update requests, the user id is passed to exclude it from the check
                    return !$this->userFinderRepository->userWithEmailAlreadyExists(
                        $value,
                        $context['data']['id'] ?? null
                    );
                },
                'message' => __('Email already exists'),
            ])
            ->requirePresence('status', $isCreateMode, __('Key is required'))
            ->add('status', 'userStatusExists', [
                'rule' => function ($value, $context) {
                    // Check if given user status is one of the enum cases values
                    return in_array($value, UserStatus::values(), true);
                },
                'message' => __('Invalid option'),
            ])
            // Language is an optional field for creation and update
            ->requirePresence('language', false, __('Key is required'))
            ->add('language', 'languageIsAvailable', [
                'rule' => function ($value, $context) {
                    // Check if given user status is one of the enum cases values
                    return in_array($value, UserLang::values(), true);
                },
                'message' => __('Invalid option'),
            ])
            ->requirePresence('user_role_id', $isCreateMode, __('Key is required'))
            ->numeric('user_role_id', __('Invalid option'))
            ->add('user_role_id', 'exists', [
                'rule' => function ($value, $context) {
                    // Check if given user role exists
                    return $this->userRoleFinderRepository->userRoleWithIdExists($value);
                },
                'message' => __('Invalid option'),
            ])
            // Theme is only relevant for update
            ->requirePresence('theme', false, __('Key is required'))
            ->add('theme', 'themeIsAvailable', [
                'rule' => function ($value, $context) {
                    // Check if given user status is one of the enum cases values
                    return in_array($value, UserTheme::values(), true);
                },
                'message' => __('Invalid option'),
            ]);
        // Add password validation rules to validator
        $this->addPasswordValidationRules($validator, $isCreateMode);

        // Validate and throw exception if there are errors
        $errors = $validator->validate($userValues);
        if ($errors) {
            throw new ValidationException($errors);
        }
    }

    /**
     * Add password and password2 validation rules.
     * In own function as it's used by different validation methods.
     *
     * @param Validator $validator
     * @param bool $required
     * Validator doesn't have to be returned as it changes the values of the passed object reference
     */
    private function addPasswordValidationRules(Validator $validator, bool $required = true): void
    {
        $validator
            ->requirePresence('password', $required, __('Key is required'))
            ->minLength('password', 3, __('Minimum length is 3'))
            ->maxLength('password', 1000, __('Maximum length is 1000'))
            ->requirePresence('password2', $required, __('Key is required'))
            ->minLength('password2', 3, __('Minimum length is 3'))
            ->maxLength('password2', 1000, __('Maximum length is 1000'))
            ->add('password2', 'passwordsMatch', [
                'rule' => function ($value, $context) {
                    // Check if passwords match
                    return $value === $context['data']['password'];
                },
                'message' => __('Passwords do not match'),
            ]);
    }

    /**
     * Validate passwords.
     *
     * @param $passwordValues
     * @return void
     */
    public function validatePasswords($passwordValues): void
    {
        $validator = new Validator();
        // Passwords are always required when this validation method is called
        $this->addPasswordValidationRules($validator, true);
        // Validate and throw exception if there are errors
        $errors = $validator->validate($passwordValues);
        if ($errors) {
            throw new ValidationException($errors);
        }
    }


    /**
     * Validate if user inputs for the login
     * are valid.
     *
     * @param array{email: string|null, password: string|null} $userLoginValues
     *
     * @throws ValidationExceptionOld
     *
     */
    public function validateUserLogin(array $userLoginValues): void
    {
        $validator = new Validator();

        // Intentionally not validating user existence as invalid login should be vague
        $validator
            ->requirePresence('email', true, __('Key is required'))
            ->email('email', false, __('Invalid email'))
            ->requirePresence('password', true, __('Key is required'));
        // Further validating seems not very useful and could lead to issues if password validation rules
        // change and user want's to log in with a password that was created before the rule change

        // Validate and throw exception if there are errors
        $errors = $validator->validate($userLoginValues);
        if ($errors) {
            throw new ValidationException($errors);
        }
    }

    /**
     * Validate email for password recovery.
     *
     * @param string|null $email
     *
     * @return ValidationResult
     */
    public function validatePasswordResetEmail(?string $email): ValidationResult
    {
        $validationResult = new ValidationResult('There is a validation error when trying to login');

        // Intentionally not validating user existence as it would be a security flaw to tell the user if email exists
        $this->validatorNative->validateEmail($email, $validationResult, true);

        // If the validation failed, throw the exception which will be caught in the Controller
        $this->validatorNative->throwOnError($validationResult);

        return $validationResult;
    }


    /**
     * Verifies if the given password is correct.
     * Previously in own service class passwordVerifier, but it's simpler
     * to display normal validation errors in the client form.
     *
     * @param string|null $password
     * @param string $field
     * @param int $userId
     *
     * @return void
     */
    public function checkIfPasswordIsCorrect(?string $password, string $field, int $userId): void
    {
        $validationResult = new ValidationResult('There is a validation error with the password.');
        // To be correct, the password must not be null
        if ($password !== null) {
            $dbUser = $this->userFinderRepository->findUserByIdWithPasswordHash($userId);
            // If password is not correct
            if (!password_verify($password, $dbUser->passwordHash)) {
                $validationResult->setError($field, __('Incorrect password'));
            }
        } else {
            $validationResult->setError(
                $field,
                __(str_replace('_', ' ', ucfirst($field)))
                . ' ' . __('is required')
            );
        }
        $this->validatorNative->throwOnError($validationResult);
    }

    /**
     * Validate single password
     * If passwords are not empty when required is already tested in validatePasswords.
     *
     * @param string|null $password
     * @param bool $required
     * @param ValidationResult $validationResult
     * @param string $fieldName Optional e.g. password2
     */
    private function validatePassword(
        ?string $password,
        ValidationResult $validationResult,
        bool $required,
        string $fieldName = 'password'
    ): void {
        // Required check done here (and not validatePasswords) because login validation uses it as well
        if (null !== $password && '' !== $password) {
            $this->validatorNative->validateLengthMin($password, $fieldName, $validationResult, 3);
        } elseif (true === $required) {
            // If password is required
            $validationResult->setError($fieldName, __('Required'));
        }
    }

    /**
     * Validate user role select.
     *
     * @param mixed $value
     * @param ValidationResult $validationResult
     * @param bool $required
     *
     * @return void
     */
    protected function validateUserRoleId(
        mixed $value,
        ValidationResult $validationResult,
        bool $required = false
    ): void {
        if (null !== $value && '' !== $value) {
            $this->validatorNative->validateNumeric($value, 'user_role_id', $validationResult, $required);
            // Excluding soft delete false as user_role has no deleted_at
            $this->validatorNative->validateExistence((int)$value, 'user_role', $validationResult, $required, false);
        } elseif (true === $required) {
            // If it is null or empty string and required
            $validationResult->setError('user_role_id', __('Required'));
        }
    }

    /**
     * Validate user status dropdown.
     *
     * @param mixed $value
     * @param ValidationResult $validationResult
     * @param bool $required
     *
     * @return void
     */
    protected function validateUserStatus(
        UserStatus|string|null $value,
        ValidationResult $validationResult,
        bool $required = false
    ): void {
        if (null !== $value && '' !== $value) {
            if ($value instanceof UserStatus) {
                $value = $value->value;
            }
            // Check if given user status is one of the enum cases
            if (!in_array($value, UserStatus::values(), true)) {
                $validationResult->setError('status', __('Invalid option'));
            }
        } elseif (true === $required) {
            // If it is null or empty string and required
            $validationResult->setError('status', __('Required'));
        }
    }
}
