<?php


namespace App\Domain\User;

use App\Domain\Exceptions\InvalidCredentialsException;
use App\Domain\Settings;
use App\Infrastructure\User\UserRepository;
use Firebase\JWT\JWT;
use Psr\Log\LoggerInterface;

class UserService
{
    
    private UserRepository $userRepository;
    protected UserValidation $userValidation;
    protected LoggerInterface $logger;

    
    public function __construct(UserRepository $userRepository, UserValidation $userValidation,LoggerInterface $logger)
    {
        $this->userRepository = $userRepository;
        $this->userValidation = $userValidation;
        $this->logger = $logger;
    }
    
    public function findAllUsers()
    {
        $allUsers = $this->userRepository->findAllUsers();
        return $allUsers;
    }
    
    public function findUser(int $id): array
    {
        return $this->userRepository->findUserById($id);
    }

    /**
     * @param string $email
     * @return array|null
     */
    public function findUserByEmail(string $email):? array
    {
        return $this->userRepository->findUserByEmail($email);
    }

    /**
     * Insert user in database
     *
     * @param $user
     * @return string
     */
    public function createUser(User $user): string
    {
        $this->userValidation->validateUserRegistration($user);
        $user->setPassword(password_hash($user->getPassword(), PASSWORD_DEFAULT));
        return $this->userRepository->insertUser($user->toArray());
    }

    /**
     * @param User $user id MUST be in object
     * @return bool
     */
    public function updateUser(User $user): bool
    {

        $this->userValidation->validateUserUpdate($user);

        $userData = [];
        if ($user->getName()!== null) {
            $userData['name'] = $user->getName();
        }
        if ($user->getEmail() !== null) {
            $userData['email'] = $user->getEmail();
        }
        if ($user->getPassword() !== null) {
            // passwords are already identical since they were validated in UserValidation.php
            $userData['password'] = password_hash($user->getPassword(), PASSWORD_DEFAULT);
        }

        return $this->userRepository->updateuser($userData, $user->getId());
    }

    public function deleteUser($id): bool
    {
        // todo delete posts
        return $this->userRepository->deleteUser($id);
    }
    
}
