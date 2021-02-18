<?php

namespace App\Domain\User;

use App\Domain\Exceptions\ValidationException;
use App\Domain\Factory\LoggerFactory;
use App\Domain\Validation\AppValidation;
use App\Domain\Validation\ValidationResult;
use App\Infrastructure\User\UserRepository;
use Psr\Log\LoggerInterface;

/**
 * Class UserValidation
 *
 * @package App\Service\Validation
 */
class UserValidation extends AppValidation
{
    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;

    /**
     * UserValidation constructor.
     *
     * @param LoggerFactory $logger
     * @param UserRepository $userRepository
     */
    public function __construct(LoggerFactory $logger, UserRepository $userRepository)
    {
        parent::__construct(
            $logger->addFileHandler('error.log')->createInstance('user-validation')
        );
        $this->userRepository = $userRepository;
    }


    /**
     * Validate updating the user.
     *
     * @param User $user
     * @return ValidationResult
     */
    public function validateUserUpdate(User $user): ValidationResult
    {
        $validationResult = new ValidationResult('There was a validation error when trying to update a user');
        $this->validateUserExistence($user->getId(), $validationResult);

        $this->validateName($user->getName(), false, $validationResult);
        $this->validateEmail($user->getEmail(), false, $validationResult);
        $this->validatePasswords([$user->getPassword(), $user->getPassword2()], false, $validationResult);

        // If the validation failed, throw the exception that will be caught in the Controller
        $this->throwOnError($validationResult);

        return $validationResult;
    }

    /**
     * Validate registration.
     *
     * @param User $user
     * @return ValidationResult
     */
    public function validateUserRegistration(User $user): ValidationResult
    {
        // Instantiate ValidationResult Object with default message
        $validationResult = new ValidationResult('There was a validation error when trying to register');

        $this->validateName($user->getName(), true, $validationResult);
        $this->validateEmail($user->getEmail(), true, $validationResult);

        // First check if validation already failed. If not email is checked in db because we know its a valid email
        if (!$validationResult->fails() && $this->userRepository->findUserByEmail($user->getEmail())) {
            $this->logger->info('Account creation tried with existing email: "' . $user->getEmail() . '"');

            // todo remove that
            $validationResult->setError('email', 'Error in registration');

            // todo implement function to tell client that register success without actually writing something in db;
            // todo send email to user to say that someone registered with his email and that he has already an account
            // todo in email provide link to login and how the password can be changed
        }

        $this->validatePasswords([$user->getPassword(), $user->getPassword2()], true, $validationResult);

        // If the validation failed, throw the exception which will be caught in the Controller
        $this->throwOnError($validationResult);

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

        $this->validateEmail($user->getEmail(), true, $validationResult);
        $this->validatePassword($user->getPassword(), true, $validationResult);

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
     * @param string $password
     * @param bool $required
     * @param ValidationResult $validationResult
     * @param string $fieldName Optional e.g. password2
     */
    public function validatePassword(
        string $password,
        bool $required,
        ValidationResult $validationResult,
        string $fieldName = 'password'
    ): void {
        // Required check done here (and not validatePasswords) because login validation uses it as well
        if (null !== $password && '' !== $password) {
            $this->validateLengthMin($password, $fieldName, $validationResult, 3);
        } elseif (true === $required) {
            // If password is required but not given
            // the user input is faulty so bad request 400 return status is sent
            $validationResult->setIsBadRequest(true, $fieldName, 'Password required but not given');
        }
    }

    /**
     * Validate email
     *
     * @param string $email
     * @param bool $required
     * @param ValidationResult $validationResult
     */
    private function validateEmail(string $email, bool $required, ValidationResult $validationResult): void
    {
        // reversed, if true -> error
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $validationResult->setError('email', 'Email address could not be validated');
        } // Because reversed the null check is done here
        elseif (true === $required && (null === $email || '' === $email)) {
            // If email is mandatory. If it is null, the user input is faulty so bad request 400 return status is sent
            $validationResult->setIsBadRequest(true, 'email', 'email required but not given');
        }
    }

    protected function validateUserExistence($userId, ValidationResult $validationResult): void
    {
        $exists = $this->userRepository->userExists($userId);
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
        } elseif (true === $required) {
            // Name is mandatory. If it is null, the user input is faulty so bad request 400 return status is sent
            $validationResult->setIsBadRequest(true, 'name', 'name required but not given');
        }
    }



    // unused from björn original

    /**
     * Validate loading the user.
     *
     * @param string $userId
     * @return ValidationResult
     */
    public function validateGet(string $userId): ValidationResult
    {
        $validationResult = new ValidationResult('User does not exist');
        $this->validateUserExistence($userId, $validationResult);

        return $validationResult;
    }


    /**
     * Validate deletion.
     *
     * @param string $userId
     * @param string $executorId
     * @return ValidationResult
     */
    public function validateDeletion(string $userId, string $executorId): ValidationResult
    {
        $validationResult = new ValidationResult('Something went wrong');
        $this->validateUserExistence($userId, $validationResult);

        return $validationResult;
    }

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
