<?php

namespace App\Domain\User\Service;

use App\Domain\Authentication\Exception\ForbiddenException;
use App\Domain\User\Authorization\UserAuthorizationChecker;
use App\Domain\User\Enum\UserActivity;
use App\Domain\User\Repository\UserDeleterRepository;
use App\Domain\UserActivity\Service\UserActivityLogger;
use Odan\Session\SessionInterface;
use Psr\Log\LoggerInterface;

class UserDeleter
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly UserDeleterRepository $userDeleterRepository,
        private readonly SessionInterface $session,
        private readonly UserAuthorizationChecker $userAuthorizationChecker,
        private readonly UserActivityLogger $userActivityLogger,
    ) {
    }

    /**
     * Delete user service method.
     *
     * @param int $userIdToDelete
     *
     * @throws ForbiddenException
     *
     * @return bool
     */
    public function deleteUser(int $userIdToDelete): bool
    {
        // Check if it's admin or if it's its own post
        if ($this->userAuthorizationChecker->isGrantedToDelete($userIdToDelete)) {
            $isDeleted = $this->userDeleterRepository->deleteUserById($userIdToDelete);
            if ($isDeleted) {
                $this->userActivityLogger->logUserActivity(UserActivity::DELETED, 'user', $userIdToDelete);
            }

            return $isDeleted;
        }

        // Log event as this should not be able to happen with normal use. User has to manually make exact request
        $this->logger->notice(
            '403 Forbidden, user ' . $this->session->get('user_id') . ' tried to delete other user with id: '
            . $userIdToDelete
        );
        throw new ForbiddenException('Not allowed to delete user.');
    }
}
