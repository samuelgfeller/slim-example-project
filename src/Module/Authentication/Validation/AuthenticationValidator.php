<?php

namespace App\Module\Authentication\Validation;

use App\Module\Exception\Domain\ValidationException;
use App\Module\User\Repository\UserFinderRepository;
use Cake\Validation\Validator;

final readonly class AuthenticationValidator
{
    public function __construct(
        private UserFinderRepository $userFinderRepository,
    ) {
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
    public function addPasswordValidationRules(Validator $validator, bool $required = true): void
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
