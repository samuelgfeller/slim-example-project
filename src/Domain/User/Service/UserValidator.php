<?php

namespace App\Domain\User\Service;

use App\Domain\Exceptions\ValidationException;
use App\Domain\Factory\LoggerFactory;
use App\Domain\User\DTO\User;
use App\Domain\Validation\AppValidation;
use App\Domain\Validation\ValidationResult;
use App\Infrastructure\User\UserExistenceCheckerRepository;

/**
 * Class UserValidator
 */
class UserValidator extends AppValidation
{

    /**
     * UserValidator constructor.
     *
     * @param LoggerFactory $logger
     * @param UserExistenceCheckerRepository $userExistenceCheckerRepository
     */
    public function __construct(
        LoggerFactory $logger,
        private UserExistenceCheckerRepository $userExistenceCheckerRepository
    ) {
        parent::__construct(
            $logger->addFileHandler('error.log')->createInstance('user-validation')
        );
    }


    /**
     * Validate updating the user.
     *
     * @param int $userId
     * @param User $user
     * @return ValidationResult
     */
    public function validateUserUpdate(int $userId, User $user): ValidationResult
    {
        $validationResult = new ValidationResult('There was a validation error when trying to update a user');
        $this->validateUserExistence($userId, $validationResult);

        $this->validateName($user->name, false, $validationResult);
        $this->validateEmail($user->email, false, $validationResult);

        // If the validation failed, throw the exception that will be caught in the Controller
        $this->throwOnError($validationResult);

        return $validationResult;
    }

    /**
     * Validate registration.
     *
     * @param User $user
     * @return ValidationResult
     * @throws ValidationException
     */
    public function validateUserRegistration(User $user): ValidationResult
    {
        // Instantiate ValidationResult Object with default message
        $validationResult = new ValidationResult('There was a validation error when trying to register');
        // If user obj has null values that are validated, TypeError is thrown and that's correct as these are values
        // coming from the client and it needs to be checked earlier (in action) that they are set accordingly
        $this->validateName($user->name, true, $validationResult);
        $this->validateEmail($user->email, true, $validationResult);
        $this->validatePasswords([$user->password, $user->password2], true, $validationResult);

        // If the validation failed, throw the exception which will be caught in the Controller
        $this->throwOnError($validationResult); // Thrown at the end so all errors are included

        return $validationResult;
    }

    /**
     * Validate if user inputs for the login
     * are valid
     *
     * @param User $user
     * @return ValidationResult
     * @throws ValidationException
     */
    public function validateUserLogin(User $user): ValidationResult
    {
        $validationResult = new ValidationResult('There was a validation error when trying to login');

        // Intentionally not validating user existence as invalid login should be vague
        $this->validateEmail($user->email, true, $validationResult);
        $this->validatePassword($user->password, true, $validationResult);

        // If the validation failed, throw the exception which will be caught in the Controller
        $this->throwOnError($validationResult);

        return $validationResult;
    }

    /**
     * Validate password and password2
     *
     * @param array $passwords [$password, $password2]
     * @param bool $required
     * @param ValidationResult $validationResult
     */
    private function validatePasswords(array $passwords, bool $required, ValidationResult $validationResult): void
    {
        if ($passwords[0] !== $passwords[1]) {
            $validationResult->setError('passwords', 'Passwords do not match');
        }

        $this->validatePassword($passwords[0], $required, $validationResult);
        $this->validatePassword($passwords[1], $required, $validationResult, 'password2');
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
    public function validatePassword(
        ?string $password,
        bool $required,
        ValidationResult $validationResult,
        string $fieldName = 'password'
    ): void {
        // Required check done here (and not validatePasswords) because login validation uses it as well
        if (null !== $password && '' !== $password) {
            $this->validateLengthMin($password, $fieldName, $validationResult, 3);
        } elseif (true === $required) {
            // If password is required but not given
            $validationResult->setError($fieldName, 'Password required but not given');
        }
    }

    /**
     * Validate email
     *
     * @param string|null $email
     * @param bool $required
     * @param ValidationResult $validationResult
     */
    private function validateEmail(string|null $email, bool $required, ValidationResult $validationResult): void
    {
        // Email filter will fail if email is empty and if it's optional it shouldn't throw an error
        if (null !== $email && '' !== $email) {
            // reversed, if true -> error
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $validationResult->setError('email', 'Invalid email address');
            }
        } elseif (true === $required && (null === $email || '' === $email)) {
            // If it is null or empty string and required
            $validationResult->setError('email', 'Email required but not given');
        }
    }

    protected function validateUserExistence($userId, ValidationResult $validationResult): void
    {
        $exists = $this->userExistenceCheckerRepository->userExists($userId);
        if (!$exists) {
            $validationResult->setMessage('User not found');
            $validationResult->setError('user', 'User not existing');

            $this->logger->debug('Check for user (id: ' . $userId . ') that didn\'t exist in validation');
        }
    }

    /**
     * Validate Name.
     *
     * @param string $name
     * @param bool $required on update the name doesn't have to be set but on creation it has
     * @param ValidationResult $validationResult
     */
    private function validateName(string $name, bool $required, ValidationResult $validationResult): void
    {
        if (null !== $name && '' !== $name) {
            $this->validateLengthMax($name, 'name', $validationResult, 200);
            $this->validateLengthMin($name, 'name', $validationResult, 2);
        } // elseif only executed if previous "if" is falsy
        elseif (true === $required) {
            $validationResult->setError('name', 'Name required but not given');
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
