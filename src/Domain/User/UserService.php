<?php


namespace App\Domain\User;

use App\Domain\Factory\LoggerFactory;
use App\Infrastructure\Post\PostRepository;
use App\Infrastructure\User\UserRepository;
use Psr\Log\LoggerInterface;

class UserService
{
    
    private UserRepository $userRepository;
    protected UserValidation $userValidation;
    protected LoggerInterface $logger;
    protected PostRepository $postRepository;

    // Service (and repo) should be split in more specific parts if it gets too big or has a lot of dependencies
    public function __construct(UserRepository $userRepository, UserValidation $userValidation, LoggerFactory $logger,
        PostRepository $postRepository)
    {
        $this->userRepository = $userRepository;
        $this->userValidation = $userValidation;
        $this->logger = $logger->addFileHandler('error.log')
            ->createInstance('user-service');
        $this->postRepository = $postRepository;
    }
    
    public function findAllUsers()
    {
        return $this->userRepository->findAllUsers();
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
     * @param User $user
     * @return string
     */
    public function createUser(User $user): string
    {
        $this->userValidation->validateUserRegistration($user);
        $user->setPasswordHash(password_hash($user->getPassword(), PASSWORD_DEFAULT));
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
            $userData['password_hash'] = password_hash($user->getPassword(), PASSWORD_DEFAULT);
        }

        return $this->userRepository->updateUser($userData, $user->getId());
    }

    public function deleteUser($id): bool
    {
        $this->postRepository->deletePostsFromUser($id);
        return $this->userRepository->deleteUser($id);
    }
    
}
