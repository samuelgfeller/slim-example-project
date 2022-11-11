<?php

namespace App\Domain\User\Service;

use App\Domain\Exceptions\ValidationException;
use App\Domain\User\Data\UserData;
use App\Domain\Validation\ValidationResult;
use App\Domain\Validation\Validator;

/**
 * Class UserValidator
 */
class UserValidator
{

    public function __construct(
        private readonly Validator $validator,
    ) {
    }

    /**
     * Validate updating the user.
     *
     * @param int $userId
     * @param array $userValues values to change
     * @return ValidationResult
     */
    public function validateUserUpdate(int $userId, array $userValues): ValidationResult
    {
        $validationResult = new ValidationResult('There was a validation error when trying to update a user');
        // Check that user exists
        $this->validator->validateExistence($userId, 'user', $validationResult, true);

        // Using array_key_exists instead of isset as isset returns false if value is null and key exists
        if (array_key_exists('first_name', $userValues)) {
            $this->validator->validateName($userValues['first_name'], 'first_name', $validationResult, false);
        }
        if (array_key_exists('surname', $userValues)) {
            $this->validator->validateName($userValues['surname'], 'surname', $validationResult, false);
        }
        if (array_key_exists('email', $userValues)) {
            $this->validator->validateEmail($userValues['email'], $validationResult, false);
        }

        // If the validation failed, throw the exception that will be caught in the Controller
        $this->validator->throwOnError($validationResult);

        return $validationResult;
    }

    /**
     * Validate registration.
     *
     * @param UserData $user
     * @return ValidationResult
     * @throws ValidationException
     */
    public function validateUserRegistration(UserData $user): ValidationResult
    {
        // Instantiate ValidationResult Object with default message
        $validationResult = new ValidationResult('There was a validation error when trying to register');
        // If user obj has null values that are validated, TypeError is thrown and that's correct as these are values
        // coming from the client and it needs to be checked earlier (in action) that they are set accordingly
        $this->validator->validateName($user->firstName, 'first_name', true, $validationResult);
        $this->validator->validateName($user->surname, 'surname', true, $validationResult);
        $this->validator->validateEmail($user->email, true, $validationResult);
        $this->validatePasswords([$user->password, $user->password2], true, $validationResult);

        // If the validation failed, throw the exception which will be caught in the Controller
        $this->validator->throwOnError($validationResult); // Thrown at the end so all errors are included

        return $validationResult;
    }

    /**
     * Validate if user inputs for the login
     * are valid
     *
     * @param UserData $user
     * @return ValidationResult
     * @throws ValidationException
     */
    public function validateUserLogin(UserData $user): ValidationResult
    {
        $validationResult = new ValidationResult('There was a validation error when trying to login');

        // Intentionally not validating user existence as invalid login should be vague
        $this->validator->validateEmail($user->email, $validationResult, true);
        $this->validatePassword($user->password, true, $validationResult);

        // If the validation failed, throw the exception which will be caught in the Controller
        $this->validator->throwOnError($validationResult);

        return $validationResult;
    }

    /**
     * Validate email for password recovery
     *
     * @param UserData $user
     * @return ValidationResult
     * @throws ValidationException
     */
    public function validatePasswordResetEmail(UserData $user): ValidationResult
    {
        $validationResult = new ValidationResult('There was a validation error when trying to login');

        // Intentionally not validating user existence as it would be a security flaw to tell the user if email exists
        $this->validator->validateEmail($user->email, true, $validationResult);

        // If the validation failed, throw the exception which will be caught in the Controller
        $this->throwOnError($validationResult);

        return $validationResult;
    }


    /**
     * Validate password and password2
     *
     * (used for password change from profile, forgotten password and registration)
     *
     * @param array $passwords [$password, $password2]
     * @param bool $required
     * @param ValidationResult|null $validationResult
     */
    public function validatePasswords(array $passwords, bool $required, ValidationResult $validationResult = null): void
    {
        // Keep value to throw exception if validationResult not given
        $validationResultIsGiven = (bool)$validationResult;
        // Instantiate ValidationResult Object with default message if not already given
        $validationResult = $validationResult ??
            new ValidationResult('There was a validation with the passwords.');

        if ($passwords[0] !== $passwords[1]) {
            $validationResult->setError('passwords', 'Passwords do not match');
        }

        $this->validatePassword($passwords[0], $required, $validationResult);
        $this->validatePassword($passwords[1], $required, $validationResult, 'password2');

        if ($validationResultIsGiven === false) {
            // If the validation failed, throw the exception which will be caught in the Controller
            $this->validator->throwOnError($validationResult); // Thrown at the end so all errors are included
        }
    }

    /**
     * Validate single password
     * If passwords are not empty when required is already tested in validatePasswords
     *
     * @param string|null $password
     * @param bool $required
     * @param ValidationResult $validationResult
     * @param string $fieldName Optional e.g. password2
     */
    private function validatePassword(
        ?string $password,
        bool $required,
        ValidationResult $validationResult,
        string $fieldName = 'password'
    ): void {
        // Required check done here (and not validatePasswords) because login validation uses it as well
        if (null !== $password && '' !== $password) {
            $this->validator->validateLengthMin($password, $fieldName, $validationResult, 3);
        } elseif (true === $required) {
            // If password is required but not given
            $validationResult->setError($fieldName, 'Password required but not given');
        }
    }

    protected function validateUserExistence($userId, ValidationResult $validationResult): void
    {
        $exists = $this->userExistenceCheckerRepository->rowExists($userId);
        if (!$exists) {
            $validationResult->setMessage('User not found');
            $validationResult->setError('user', 'User not existing');

            $this->logger->debug('Check for user (id: ' . $userId . ') that didn\'t exist in validation');
        }
    }



    // unused from björn original

//    /**
//     * Validate loading the user.
//     *
//     * @param string $userId
//     * @return ValidationResult
//     */
//    public function validateGet(string $userId): ValidationResult
//    {
//        $validationResult = new ValidationResult('User does not exist');
//        $this->validateUserExistence($userId, $validationResult);
//
//        return $validationResult;
//    }
//
//
//    /**
//     * Validate deletion.
//     *
//     * @param string $userId
//     * @param string $executorId
//     * @return ValidationResult
//     */
//    public function validateDeletion(string $userId, string $executorId): ValidationResult
//    {
//        $validationResult = new ValidationResult('Something went wrong');
//        $this->validateUserExistence($userId, $validationResult);
//
//        return $validationResult;
//    }

//    /**
//     * Validate username.
//     *
//     * @param string $username
//     * @param ValidationResult $validationResult
//     */
//    private function validateUsername(string $username, ValidationResult $validationResult)
//    {
//        $this->validateLengthMax($username, 'username', $validationResult, 80);
//        $this->validateLengthMin($username, 'username', $validationResult, 3);
//        if ($validationResult->fails()) {
//            return;
//        }
//        if ($this->userRepository->existsUserByUsername($username)) {
//            $validationResult->setError('username', __('Username already taken'));
//        }
//        if (preg_match('/((^|, )(admin|user|nicola|bjoern|björn|penis|69|420))+$/', $username)) {
//            $validationResult->setError('username', __('OOOOH you filthy one...'));
//        }
//    }
//
//    /**
//     * Validate the old password
//     *
//     * @param string $userId
//     * @param string $oldPassword
//     * @param ValidationResult $validationResult
//     * @return void
//     */
//    private function validateOldPassword(string $userId, string $oldPassword, ValidationResult $validationResult)
//    {
//        $correctPassword = $this->userRepository->checkPassword($userId, $oldPassword);
//        if (!$correctPassword) {
//            $validationResult->setError('passwordOld', __('Does not match the old password'));
//        }
//    }
//
//
//    /**
//     * Validate firstname.
//     *
//     * @param string $firstName
//     * @param ValidationResult $validationResult
//     */
//    private function validateFirstname(string $firstName, ValidationResult $validationResult)
//    {
//        $this->validateLengthMax($firstName, 'firstname', $validationResult, 80);
//        $this->validateLengthMin($firstName, 'firstname', $validationResult, 3);
//    }
//
//    /**
//     * Validate lastname.
//     *
//     * @param string $lastName
//     * @param ValidationResult $validationResult
//     */
//    private function validateLastname(string $lastName, ValidationResult $validationResult)
//    {
//        $this->validateLengthMax($lastName, 'lastname', $validationResult, 80);
//        $this->validateLengthMin($lastName, 'lastname', $validationResult, 3);
//    }
}
