<?php

namespace App\Domain\User\Service;

use App\Domain\User\Data\UserData;
use App\Domain\User\Enum\UserStatus;
use App\Domain\Validation\ValidationException;
use App\Domain\Validation\ValidationResult;
use App\Domain\Validation\Validator;
use App\Infrastructure\User\UserFinderRepository;

/**
 * Class UserValidator.
 */
class UserValidator
{
    public function __construct(
        private readonly Validator $validator,
        private readonly UserFinderRepository $userFinderRepository,
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
        $this->validator->validateExistence($userId, 'user', $validationResult, true);

        // Using array_key_exists instead of isset as isset returns false if value is null and key exists
        if (array_key_exists('first_name', $userValues)) {
            $this->validator->validateName($userValues['first_name'], 'first_name', $validationResult, true);
        }
        if (array_key_exists('surname', $userValues)) {
            $this->validator->validateName($userValues['surname'], 'surname', $validationResult, true);
        }
        if (array_key_exists('email', $userValues)) {
            if ($this->validator->resourceExistenceCheckerRepository->rowExists(
                ['email' => $userValues['email'], 'id !=' => $userId],
                'user'
            )) {
                $validationResult->setError('email', 'User with this email already exists');
            }
            $this->validator->validateEmail($userValues['email'], $validationResult, true);
        }
        if (array_key_exists('status', $userValues)) {
            $this->validateUserStatus($userValues['status'], $validationResult, true);
        }
        if (array_key_exists('user_role_id', $userValues)) {
            $this->validateUserRoleId($userValues['user_role_id'], $validationResult, true);
        }

        // If the validation failed, throw the exception that will be caught in the Controller
        $this->validator->throwOnError($validationResult);

        return $validationResult;
    }

    /**
     * Validate registration.
     *
     * @param UserData $user
     *
     * @throws ValidationException|\JsonException
     *
     * @return ValidationResult
     */
    public function validateUserCreation(UserData $user): ValidationResult
    {
        // Instantiate ValidationResult Object with default message
        $validationResult = new ValidationResult('There is a validation error when trying to register a user');

        if ($this->validator->resourceExistenceCheckerRepository->rowExists(['email' => $user->email], 'user')) {
            $validationResult->setError('email', 'User with this email already exists');
        }
        $this->validator->validateName($user->firstName, 'first_name', $validationResult, true);
        $this->validator->validateName($user->surname, 'surname', $validationResult, true);
        $this->validator->validateEmail($user->email, $validationResult, true);
        $this->validateUserStatus($user->status, $validationResult, true);
        $this->validateUserRoleId($user->userRoleId, $validationResult, true);
        $this->validatePasswords([$user->password, $user->password2], true, $validationResult);

        // If the validation failed, throw the exception which will be caught in the Action
        $this->validator->throwOnError($validationResult);

        return $validationResult;
    }

    /**
     * Validate if user inputs for the login
     * are valid.
     *
     * @param array{email: string|null, password: string|null} $userLoginValues
     *
     * @throws ValidationException|\JsonException
     *
     * @return ValidationResult
     */
    public function validateUserLogin(array $userLoginValues): ValidationResult
    {
        $validationResult = new ValidationResult('There is a validation error when trying to login');

        // Intentionally not validating user existence as invalid login should be vague
        $this->validator->validateEmail($userLoginValues['email'] ?? null, $validationResult, true);
        $this->validatePassword($userLoginValues['password'] ?? null, $validationResult, true);

        // If the validation failed, throw the exception which will be caught in the Controller
        $this->validator->throwOnError($validationResult);

        return $validationResult;
    }

    /**
     * Validate email for password recovery.
     *
     * @param string|null $email
     *
     * @throws \JsonException
     *
     * @return ValidationResult
     */
    public function validatePasswordResetEmail(?string $email): ValidationResult
    {
        $validationResult = new ValidationResult('There is a validation error when trying to login');

        // Intentionally not validating user existence as it would be a security flaw to tell the user if email exists
        $this->validator->validateEmail($email, $validationResult, true);

        // If the validation failed, throw the exception which will be caught in the Controller
        $this->validator->throwOnError($validationResult);

        return $validationResult;
    }

    /**
     * Validate password and password2.
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
            new ValidationResult('There is a validation error with the passwords.');

        if ($passwords[0] !== $passwords[1]) {
            $validationResult->setError('password2', 'Passwords do not match');
        }

        $this->validatePassword($passwords[0], $validationResult, $required);
        $this->validatePassword($passwords[1], $validationResult, $required, 'password2');

        if ($validationResultIsGiven === false) {
            // If the validation failed, throw the exception which will be caught in the Controller
            $this->validator->throwOnError($validationResult); // Thrown at the end so all errors are included
        }
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
                $validationResult->setError($field, 'Incorrect password');
            }
        } else {
            $validationResult->setError(
                $field,
                str_replace('_', ' ', ucfirst($field))
                . ' required'
            );
        }
        $this->validator->throwOnError($validationResult);
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
            $this->validator->validateLengthMin($password, $fieldName, $validationResult, 3);
        } elseif (true === $required) {
            // If password is required
            $validationResult->setError($fieldName, 'Password is required');
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
            $this->validator->validateNumeric($value, 'user_role_id', $validationResult, $required);
            // Excluding soft delete false as user_role has no deleted_at
            $this->validator->validateExistence((int)$value, 'user_role', $validationResult, $required, false);
        } elseif (true === $required) {
            // If it is null or empty string and required
            $validationResult->setError('user_role_id', 'User role is required');
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
                $validationResult->setError('status', 'Status not existing');
            }
        } elseif (true === $required) {
            // If it is null or empty string and required
            $validationResult->setError('status', 'Status is required');
        }
    }
}
