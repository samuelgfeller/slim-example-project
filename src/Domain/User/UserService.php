<?php


namespace App\Domain\User;

use App\Domain\Factory\LoggerFactory;
use App\Domain\Utility\EmailService;
use App\Infrastructure\Post\PostRepository;
use App\Infrastructure\User\UserRepository;
use Psr\Log\LoggerInterface;

class UserService
{

    protected LoggerInterface $logger;

    // Service (and repo) should be split in more specific parts if it gets too big or has a lot of dependencies
    public function __construct(
        private UserRepository $userRepository,
        protected UserValidation $userValidation,
        LoggerFactory $logger,
        protected PostRepository $postRepository
    ) {
        $this->logger = $logger->addFileHandler('error.log')->createInstance('user-service');
    }

    public function findAllUsers()
    {
        return $this->userRepository->findAllUsers();
    }

    public function findUser(string $id): array
    {
        return $this->userRepository->findUserById($id);
    }

    /**
     * Find active user via email
     *
     * @param string $email
     * @return array|null
     */
    public function findUserByEmail(string $email): ?array
    {
        return $this->userRepository->findUserByEmail($email);
    }

    /**
     * @param User $user id MUST be in object
     * @return bool
     */
    public function updateUser(User $user): bool
    {
        $this->userValidation->validateUserUpdate($user);

        $userData = [];
        if ($user->getName() !== null) {
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
