<?php


namespace App\Domain\User;

use App\Domain\Exceptions\ForbiddenException;
use App\Domain\Factory\LoggerFactory;
use App\Infrastructure\Post\PostRepository;
use App\Infrastructure\User\UserRepository;
use Psr\Log\LoggerInterface;

class UserService
{

    protected LoggerInterface $logger;

    // Service (and repo) should be split in more specific parts if it gets too big or has a lot of dependencies
    public function __construct(
        private UserRepository $userRepository,
        protected UserValidator $userValidator,
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

    public function findUserById(string $id): User
    {
        return $this->userRepository->findUserById($id);
    }

    /**
     * Find active user via email
     *
     * @param string $email
     * @return User
     */
    public function findUserByEmail(string $email): User
    {
        return $this->userRepository->findUserByEmail($email);
    }

    /**
     * Update user values.
     * This function is intended for changes coming from a user
     * therefore it changes only "user changeable" general info (not password)
     *
     * @param int $userIdToChange user id on which the change is requested to be made
     * @param array $userValues values to change
     * @param int $loggedInUserId
     * @return bool
     */
    public function updateUser(int $userIdToChange, array $userValues, int $loggedInUserId): bool
    {
        $user = new User($userValues, true);
        $this->userValidator->validateUserUpdate($userIdToChange, $user);

        $userRole = $this->userRepository->getUserRoleById($loggedInUserId);
        // Check if it's admin or if it's its own user
        if ($userRole === 'admin' || $userIdToChange === $loggedInUserId) {
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
            return $this->userRepository->updateUser($userIdToChange, $validUpdateValues);
        }

        // User does not have needed rights to access area or function
        $this->logger->notice(
            'User ' . $loggedInUserId . ' tried to update other user with id: ' . $userIdToChange
        );
        throw new ForbiddenException('Not allowed to change that user');
    }

    public function deleteUser($id): bool
    {
        $this->postRepository->deletePostsFromUser($id);
        return $this->userRepository->deleteUserById($id);
    }

}
