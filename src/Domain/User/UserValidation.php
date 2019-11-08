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
    public function __construct(LoggerInterface $logger, UserRepositoryInterface $userRepositoryInterface) {
        parent::__construct($logger);
        $this->userRepositoryInterface = $userRepositoryInterface;
    }

    /**
     * @param string $userId
     * @param ValidationResult $validationResult
     */
    protected function validateUser(string $userId, ValidationResult $validationResult)
    {
        $exists = $this->userRepositoryInterface->existsUser($userId);
        if (!$exists) {
            $validationResult->setMessage('You are not a registered user!');
            $validationResult->setError('user', __('Not registered'));
            // TODO add logging
        }
    }

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
     * Validate updating the user.
     *
     * @param string $executorId
     * @param string $userId
     * @param null|string $username
     * @param string|null $oldPassword
     * @param null|string $password
     * @param null|string $email
     * @param null|string $firstName
     * @param null|string $lastName
     * @param null|string $roleId
     * @return ValidationResult
     */
    public function validateUpdate(
        string $executorId,
        string $userId,
        ?string $username,
        ?string $oldPassword,
        ?string $password,
        ?string $email,
        ?string $firstName,
        ?string $lastName,
        ?string $roleId
    ) {
        $validationResult = new ValidationResult(__('Please check your data'));
        $this->validateUser($userId, $validationResult);

        if (!empty($username)) {
            $this->validateUsername($username, $validationResult);
        }

        if (!empty($password)) {
            $this->validateOldPassword($userId, $oldPassword, $validationResult);
            $this->validatePassword($password, $validationResult);
        }

        if (!empty($email)) {
            $this->validateEmail($email, $validationResult);
        }

        if (!empty($firstName)) {
            $this->validateFirstname($firstName, $validationResult);
        }

        if (!empty($lastName)) {
            $this->validateLastname($lastName, $validationResult);
        }

        if (!empty($roleId) && !$this->hasPermissionLevel($executorId, RoleLevel::SUPER_ADMIN)) {
            $validationResult->setError('permission', 'You do not have the permission to execute this action');
        }

        return $validationResult;
    }

    /**
     * Validate registration.
     *
     * @param $userData
     * @return ValidationResult
     */
    public function validateRegister($userData): ValidationResult {
        $validationResult = new ValidationResult('Your registration is not correct');
        $this->validateFirstname($userData['firstname'], $validationResult);
        $this->validateLastname($userData['lastname'], $validationResult);
        $this->validateEmail($userData['email'], $validationResult);
        $this->validatePassword($userData['password'], $validationResult);

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
        if ((int)$executorId !== (int)$userId) {
            $this->validatePermissionLevel($executorId, RoleLevel::SUPER_ADMIN, $validationResult);
        }

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
     * Validate password
     *
     * @param string $password
     * @param ValidationResult $validationResult
     */
    private function validatePassword(string $password, ValidationResult $validationResult)
    {
        $this->validateLengthMax($password, 'password', $validationResult);
        $this->validateLengthMin($password, 'password', $validationResult, 6);
        if (!preg_match('/(?=.*[A-Z])(?=.*[a-z]).*/', $password)) {
            $validationResult->setError('password',
                __('Password must contain at least one uppercase and one lowercase letter'));
        }
    }

    /**
     * Validate email
     *
     * @param string $email
     * @param ValidationResult $validationResult
     */
    private function validateEmail(string $email, ValidationResult $validationResult)
    {
        if (!is_email($email)) {
            $validationResult->setError('email', __('Not a valid email address'));
        }
        if ($this->userRepositoryInterface->existsUserByEmail($email)) {
            $validationResult->setError('email', __('Email already registered'));
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
