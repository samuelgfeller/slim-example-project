<?php

namespace App\Domain\User;

use App\Domain\User\UserRepositoryInterface;
use App\Domain\Validation\AppValidation;
use App\Domain\Validation\ValidationResult;
use Psr\Log\LoggerInterface;

/**
 * Class UserValidation
 *
 * @package App\Service\Validation
 */
class UserValidation extends AppValidation
{
    /**
     * @var UserRepositoryInterface
     */
    private $userRepositoryInterface;

    /**
     * UserValidation constructor.
     *
     * @param LoggerInterface $logger
     * @param UserRepositoryInterface $userRepositoryInterface
     */
    public function __construct(LoggerInterface $logger, UserRepositoryInterface $userRepositoryInterface)
    {
        parent::__construct($logger);
        $this->userRepositoryInterface = $userRepositoryInterface;
    }


    /**
     * Validate updating the user.
     *
     * @param int $id
     * @param array $userData
     * @return ValidationResult
     */
    public function validateUserUpdate(int $id, array $userData): ValidationResult
    {
        $validationResult = new ValidationResult('There was a validation error when trying to update a user');
        $this->validateUser($id, $validationResult);

        if (isset($userData['name'])){
            $this->validateName($userData['name'], $validationResult);
        }
        if (isset($userData['email'])){
            $this->validateEmail($userData['email'], $validationResult);
        }
        if (isset($userData['password'],$userData['password2'])){
            $this->validatePasswords([$userData['password'],$userData['password2']], $validationResult);
        }

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
        $validationResult = new ValidationResult('There was a validation error when trying to register a user');

        $this->validateName($user->getName(), true, $validationResult);
        $this->validateEmail($user->getEmail(), true, $validationResult);

        // First check if validation already failed. If not email is checked in db because we know its a valid email
        if (!$validationResult->fails() && $this->userRepositoryInterface->findUserByEmail($user->getEmail())) {
            $this->logger->info('Account creation tried with existing email: "' . $user->getEmail() . '"');

            // todo remove that
            $validationResult->setError('email', 'Error in registration');

            // todo implement function to tell client that register success without actually writing something in db;
            // todo send email to user to say that someone registered with his email and that he has already an account
            // todo in email provide link to login and how the password can be changed
        }

        $this->validatePasswords([$user->getPassword(), $user->getPassword2()], $validationResult);

        // If the validation failed, throw the exception which will be caught in the Controller
        $this->throwOnError($validationResult);

        return $validationResult;
    }

    public function validateUserLogin($rawData)
    {
        $validationResult = new ValidationResult('There was a validation error when trying to login a user');

        if (isset($rawData['email'],$rawData['password'])) {
            $this->validateEmail($rawData['email'], $validationResult);

            $loginData = [
                'email' => filter_var($rawData['email'], FILTER_VALIDATE_EMAIL),
                'password' => $rawData['password']
            ];
            $validationResult->setValidatedData($loginData);
            return $validationResult;
        }
        $validationResult->setIsBadRequest(true);
        return $validationResult;
    }

    /**
     * Validate password
     *
     * @param array $passwords [$password, $password2]
     * @param ValidationResult $validationResult
     */
    private function validatePasswords(array $passwords, ValidationResult $validationResult)
    {
        if ($passwords[0] !== $passwords[1]) {
            $validationResult->setError('password2', 'Passwords do not match');
        }
        $this->validateLengthMin($passwords[0], 'password', $validationResult, 3);
        $this->validateLengthMin($passwords[1], 'password2', $validationResult, 3);
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
        } elseif (true === $required) {
            // If email is mandatory. If it is null, the user input is faulty so bad request 400 return status is sent
            $validationResult->setIsBadRequest(true);
        }
    }

    protected function validateUser($userId, ValidationResult $validationResult): void
    {
        $exists = $this->userRepositoryInterface->userExists($userId);
        if (!$exists) {
            $validationResult->setMessage('User not found');
            $validationResult->setError('user', 'User not existing');

            $this->logger->info('Check for user (id: '.$userId.') that didn\'t exist in validation');

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
        if (null !== $name) {
            $this->validateLengthMax($name, 'name', $validationResult, 200);
            $this->validateLengthMin($name, 'name', $validationResult, 2);
        } elseif (true === $required) {
            // Name is mandatory. If it is null, the user input is faulty so bad request 400 return status is sent
            $validationResult->setIsBadRequest(true);
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
        $this->validateUser($userId, $validationResult);

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
        $this->validateUser($userId, $validationResult);

        return $validationResult;
    }

    /**
     * Validate username.
     *
     * @param string $username
     * @param ValidationResult $validationResult
     */
    private function validateUsername(string $username, ValidationResult $validationResult)
    {
        $this->validateLengthMax($username, 'username', $validationResult, 80);
        $this->validateLengthMin($username, 'username', $validationResult, 3);
        if ($validationResult->fails()) {
            return;
        }
        if ($this->userRepositoryInterface->existsUserByUsername($username)) {
            $validationResult->setError('username', __('Username already taken'));
        }
        if (preg_match('/((^|, )(admin|user|nicola|bjoern|björn|penis|69|420))+$/', $username)) {
            $validationResult->setError('username', __('OOOOH you filthy one...'));
        }
    }

    /**
     * Validate the old password
     *
     * @param string $userId
     * @param string $oldPassword
     * @param ValidationResult $validationResult
     * @return void
     */
    private function validateOldPassword(string $userId, string $oldPassword, ValidationResult $validationResult)
    {
        $correctPassword = $this->userRepositoryInterface->checkPassword($userId, $oldPassword);
        if (!$correctPassword) {
            $validationResult->setError('passwordOld', __('Does not match the old password'));
        }
    }


    /**
     * Validate firstname.
     *
     * @param string $firstName
     * @param ValidationResult $validationResult
     */
    private function validateFirstname(string $firstName, ValidationResult $validationResult)
    {
        $this->validateLengthMax($firstName, 'firstname', $validationResult, 80);
        $this->validateLengthMin($firstName, 'firstname', $validationResult, 3);
    }

    /**
     * Validate lastname.
     *
     * @param string $lastName
     * @param ValidationResult $validationResult
     */
    private function validateLastname(string $lastName, ValidationResult $validationResult)
    {
        $this->validateLengthMax($lastName, 'lastname', $validationResult, 80);
        $this->validateLengthMin($lastName, 'lastname', $validationResult, 3);
    }
}
