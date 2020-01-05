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
        $validationResult = new ValidationResult('Please check your data');
        $this->validateUser($id, $validationResult);

        if (isset($userData['name'])){
            $this->validateName($userData['name'], $validationResult);
        }
        if (isset($userData['email'])){
            $this->validateEmail($userData['email'], $validationResult);
        }
        if (isset($userData['password1'],$userData['password2'])){
            $this->validatePasswords([$userData['password1'],$userData['password2']], $validationResult);
        }

        return $validationResult;
    }

    /**
     * Validate registration.
     *
     * @param $userData
     * @return ValidationResult
     */
    public function validateUserRegistration($userData): ValidationResult
    {
        $validationResult = new ValidationResult('There is something in the registration data which couldn\'t be validated');
        $this->validateName($userData['email'], $validationResult);
        $this->validateEmail($userData['email'], $validationResult);

        // First check if validation already failed. If not email is checked in db because we know its a valid email
        if (!$validationResult->fails() && $this->userRepositoryInterface->findUserByEmail($userData['email'])) {
            $this->logger->info('Account creation tried with existing email: ' . $userData['email']);

            // todo remove that
            $validationResult->setError('email', 'Error in registration');

            // todo implement function to tell client that register success without actually writing something in db;
            // todo send email to user to say that someone registered with his email and that he has already an account
            // todo in email provide link to login and how the password can be changed
        }

        $this->validatePasswords([$userData['password1'], $userData['password2']], $validationResult);
        return $validationResult;
    }



    /**
     * Validate password
     *
     * @param array $passwords [$password1, $password2]
     * @param ValidationResult $validationResult
     */
    private function validatePasswords(array $passwords, ValidationResult $validationResult)
    {
        if ($passwords[0] !== $passwords[1]) {
            $validationResult->setError('password2', 'Passwords do not match');
        }
        $this->validateLengthMin($passwords[0], 'password1', $validationResult, 3);
        $this->validateLengthMin($passwords[1], 'password2', $validationResult, 3);
    }

    /**
     * Validate email
     *
     * @param string $email
     * @param ValidationResult $validationResult
     */
    private function validateEmail(string $email, ValidationResult $validationResult): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $validationResult->setError('email', 'Email address could not be validated');
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
     * @param ValidationResult $validationResult
     */
    private function validateName(string $name, ValidationResult $validationResult): void
    {
        $this->validateLengthMax($name, 'name', $validationResult, 200);
        $this->validateLengthMin($name, 'name', $validationResult, 2);
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
