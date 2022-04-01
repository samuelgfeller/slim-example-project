<?php


namespace App\Domain\User\Service;


use App\Domain\Exceptions\ForbiddenException;
use App\Domain\Factory\LoggerFactory;
use App\Infrastructure\Authentication\UserRoleFinderRepository;
use App\Infrastructure\Post\PostDeleterRepository;
use App\Infrastructure\User\UserDeleterRepository;
use Odan\Session\SessionInterface;
use Psr\Log\LoggerInterface;

class UserDeleter
{
    protected LoggerInterface $logger;

    /**
     * @param PostDeleterRepository $postDeleterRepository
     * @param UserDeleterRepository $userDeleterRepository
     * @param UserRoleFinderRepository $userRoleFinderRepository
     * @param LoggerFactory $logger
     */
    public function __construct(
        LoggerFactory $logger,
        private UserDeleterRepository $userDeleterRepository,
        private UserRoleFinderRepository $userRoleFinderRepository,
        private PostDeleterRepository $postDeleterRepository,
        private SessionInterface $session,
    ) {
        $this->logger = $logger->addFileHandler('error.log')->createInstance('user-delete');
    }

    /**
     * Delete user service method
     *
     * @param int $userIdToDelete
     * @param int $loggedInUserId
     * @return bool
     * @throws ForbiddenException
     */
    public function deleteUser(int $userIdToDelete, int $loggedInUserId): bool
    {
        $userRole = $this->userRoleFinderRepository->getUserRoleById($loggedInUserId);
        // Check if it's admin or if it's its own post
        if ($userRole === 'admin' || $userIdToDelete === $loggedInUserId) {
            // Delete attached posts and user
            $this->postDeleterRepository->deletePostsFromUser($userIdToDelete);
            $isDeleted = $this->userDeleterRepository->deleteUserById($userIdToDelete);

            // Only if user is admin, inform that the user was not deleted
            if (!$isDeleted) {
                $this->session->getFlash()->add('warning', 'The account was not deleted');
            }

            return $isDeleted;
        }

        // Log event as this should not be able to happen with normal use. User has to manually make exact request
        $this->logger->notice(
            '403 Forbidden, user ' . $loggedInUserId . ' tried to delete other user with id: ' . $userIdToDelete
        );
        throw new ForbiddenException('Not allowed to change that post as it\'s linked to another user.');
    }
}