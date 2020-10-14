<?php


namespace App\Domain\User;

use App\Infrastructure\Post\PostRepository;
use App\Infrastructure\User\UserRepository;
use Psr\Log\LoggerInterface;

class UserService
{
    
    private UserRepository $userRepository;
    protected UserValidation $userValidation;
    protected LoggerInterface $logger;
    protected PostRepository $postRepository;

    
    public function __construct(UserRepository $userRepository, UserValidation $userValidation, LoggerInterface $logger,
        PostRepository $postRepository)
    {
        $this->userRepository = $userRepository;
        $this->userValidation = $userValidation;
        $this->logger = $logger;
        $this->postRepository = $postRepository;
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
        return $this->userRepository->insertUser($user->toArrayForDatabase());
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
        // todo test with empty email to see if it would remove the email
        if ($user->getEmail() !== null) {
            $userData['email'] = $user->getEmail();
        }
        if ($user->getPassword() !== null) {
            // passwords are already identical since they were validated in UserValidation.php
            $userData['password'] = password_hash($user->getPassword(), PASSWORD_DEFAULT);
        }

        return $this->userRepository->updateUser($userData, $user->getId());
    }

    public function deleteUser($id): bool
    {
        $this->postRepository->deletePostsFromUser($id);
        return $this->userRepository->deleteUser($id);
    }
    
}
