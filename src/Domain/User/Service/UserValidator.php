<?php

namespace App\Domain\User\Service;

use App\Domain\Authentication\Repository\UserRoleFinderRepository;
use App\Domain\Exception\ValidationException;
use App\Domain\User\Enum\UserLang;
use App\Domain\User\Enum\UserStatus;
use App\Domain\User\Enum\UserTheme;
use App\Domain\User\Repository\UserFinderRepository;
use Cake\Validation\Validator;

final readonly class UserValidator
{
    public function __construct(
        private UserFinderRepository $userFinderRepository,
        private UserRoleFinderRepository $userRoleFinderRepository,
    ) {
    }

    /**
     * Validate user registration and update.
     *
     * @param array $userValues
     * @param bool $isCreateMode
     */
    public function validateUserValues(array $userValues, bool $isCreateMode = true): void
    {
        $validator = new Validator();

        // Cake validation library automatically sets a rule that fields cannot be null as soon as there is any
        // validation rule set for the field. This is why we have to allowEmptyString to allow null.

        // For the user, there are no optional fields meaning that if any field is passed, it has to contain a value.
        $validator
            // First name and lastName are required to have values if they're given so no allowEmptyString
            ->requirePresence('first_name', $isCreateMode, __('Field is required'))
            ->minLength('first_name', 2, __('Minimum length is %d', 2))
            ->maxLength('first_name', 100, __('Maximum length is %d', 100))
            // Disallow empty strings as field is required
            ->notEmptyString('first_name', __('Required'))
            ->requirePresence('last_name', $isCreateMode, __('Field is required'))
            ->minLength('last_name', 2, __('Minimum length is %d', 2))
            ->maxLength('last_name', 100, __('Maximum length is %d', 100))
            ->notEmptyString('last_name', __('Required'))
            ->requirePresence('email', $isCreateMode, __('Field is required'))
            // email() automatically disallows empty strings
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
            ->requirePresence('status', $isCreateMode, __('Field is required'))
            ->add('status', 'userStatusExists', [
                'rule' => function ($value, $context) {
                    // Check if given user status is one of the enum cases values
                    return in_array($value, UserStatus::values(), true);
                },
                'message' => __('Invalid option'),
            ])
            // Language is an optional field for creation and update
            ->requirePresence('language', false, __('Field is required'))
            ->add('language', 'languageIsAvailable', [
                'rule' => function ($value, $context) {
                    // Check if given user status is one of the enum cases values
                    return in_array($value, UserLang::values(), true);
                },
                'message' => __('Invalid option'),
            ])
            ->requirePresence('user_role_id', $isCreateMode, __('Field is required'))
            ->numeric('user_role_id', __('Invalid option'))
            ->add('user_role_id', 'exists', [
                'rule' => function ($value, $context) {
                    // Check if given user role exists
                    return $this->userRoleFinderRepository->userRoleWithIdExists($value);
                },
                'message' => __('Invalid option'),
            ])
            // Theme is only relevant in update
            ->requirePresence('theme', false, __('Field is required'))
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
     * Validate passwords.
     *
     * @param array $passwordValues
     *
     * @return void
     */
    public function validatePasswordChange(array $passwordValues): void
    {
        $validator = new Validator();
        // Passwords are always required when this validation method is called
        $this->addPasswordValidationRules($validator, true);
        // No rule for old password as it's optional and validated later in the service class

        // Validate and throw exception if there are errors
        $errors = $validator->validate($passwordValues);
        if ($errors) {
            throw new ValidationException($errors);
        }
    }

    /**
     * Validate passwords.
     *
     * @param array $passwordResetValues
     *
     * @return void
     */
    public function validatePasswordReset(array $passwordResetValues): void
    {
        $validator = new Validator();
        // Passwords are always required when this validation method is called
        $this->addPasswordValidationRules($validator, true);
        // Add token validation rules
        $validator
            ->requirePresence('id', true, __('Field is required'))
            ->numeric('id', __('Token id is not numeric'))
            ->requirePresence('token', true, __('Field is required'))
            ->notEmptyString('token', __('Token is required'));

        // Validate and throw exception if there are errors
        $errors = $validator->validate($passwordResetValues);
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
            ->requirePresence('password', $required, __('Field is required'))
            ->notEmptyString('password', __('Password required'))
            ->minLength('password', 3, __('Minimum length is %d', 3))
            ->maxLength('password', 1000, __('Maximum length is %d', 1000))
            ->requirePresence('password2', $required, __('Field is required'))
            ->notEmptyString('password2', __('Password required'))
            ->minLength('password2', 3, __('Minimum length is %d', 3))
            ->maxLength('password2', 1000, __('Maximum length is %d', 1000))
            ->add('password2', 'passwordsMatch', [
                'rule' => function ($value, $context) {
                    // Check if passwords match
                    return $value === $context['data']['password'];
                },
                'message' => __('Passwords do not match'),
            ]);
    }

    /**
     * Validate if user inputs for the login
     * are valid.
     *
     * @param array $userLoginValues
     *
     * @throws ValidationException
     */
    public function validateUserLogin(array $userLoginValues): void
    {
        $validator = new Validator();

        // Intentionally not validating user existence as invalid login should be vague
        $validator
            ->requirePresence('email', true, __('Field is required'))
            ->email('email', false, __('Invalid email'))
            ->requirePresence('password', true, __('Field is required'))
            ->notEmptyString('password', __('Invalid password'))
            // Further password validating seems not very useful and could lead to issues if password validation rules
            // change and user want's to log in with a password that was created before the rule change
            ->requirePresence('g-recaptcha-response', false); // Optional key

        // Validate and throw exception if there are errors
        $errors = $validator->validate($userLoginValues);
        if ($errors) {
            throw new ValidationException($errors);
        }
    }

    /**
     * Validate email for password recovery.
     *
     * @param array $userValues
     */
    public function validatePasswordResetEmail(array $userValues): void
    {
        $validator = new Validator();

        // Intentionally not validating user existence as it would be a security flaw to tell the user if email exists
        $validator->requirePresence('email', true, __('Field is required'))
            ->email('email', false, __('Invalid email'));

        // Validate and throw exception if there are errors
        $errors = $validator->validate($userValues);
        if ($errors) {
            throw new ValidationException($errors);
        }
    }

    /**
     * Verifies if the given old password is correct.
     * Previously in own service class passwordVerifier, but it's simpler
     * to display normal validation errors in the client form.
     *
     * @param array $oldPassword array with as key old_password
     * @param int $userId
     *
     * @return void
     */
    public function checkIfOldPasswordIsCorrect(array $oldPassword, int $userId): void
    {
        $validator = new Validator();

        $validator
            // If this validation method is called, we already know that the key is present
            ->notEmptyString('old_password', __('Old password required'))
            ->add('old_password', 'oldPasswordIsCorrect', [
                'rule' => function ($value, $context) use ($userId) {
                    // Get password from database
                    $dbUser = $this->userFinderRepository->findUserByIdWithPasswordHash($userId);

                    // Check if old password is correct
                    return password_verify($value, (string)$dbUser->passwordHash);
                },
                'message' => __('Incorrect password'),
            ]);

        // Validate and throw exception if there are errors
        $errors = $validator->validate($oldPassword);
        if ($errors) {
            throw new ValidationException($errors);
        }
    }
}
