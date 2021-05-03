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

    /**
     * @return User[]
     */
    public function findAllUsers(): array
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
     * Update user values.
     * This function is intended for changes coming from a user
     * therefore it changes only "user changeable" general info (not password)
     *
     * @param int $userId
     * @param array $userValues values to change
     * @return bool
     */
    public function updateUser(int $userId, array $userValues): bool
    {
        $user = new User($userValues);
        $this->userValidation->validateUserUpdate($userId, $user);

        // User values to change (cannot use object as unset values would be "null" and remove values in db)
        $validUpdateValues = [];
        // Data to be changed is set here. It can easily be controlled which data this function is allowed to change here
        // instead of removing the fields that are not allowed to be edited with this function (password, role etc.)
        if ($user->name !== null) {
            $validUpdateValues['name'] = $user->name;
        }
        if ($user->email !== null) {
            $validUpdateValues['email'] = $user->email;
        }
        return $this->userRepository->updateUser($userId, $validUpdateValues);
    }

    public function deleteUser($id): bool
    {
        $this->postRepository->deletePostsFromUser($id);
        return $this->userRepository->deleteUser($id);
    }

}
