<?php

namespace App\Module\User\Service;

use App\Module\Authorization\Exception\ForbiddenException;
use App\Module\User\Enum\UserActivity;
use App\Module\User\Repository\UserDeleterRepository;
use App\Module\User\Service\Authorization\UserPermissionVerifier;
use App\Module\UserActivity\Service\UserActivityLogger;
use Odan\Session\SessionInterface;
use Psr\Log\LoggerInterface;

final readonly class UserDeleter
{
    public function __construct(
        private LoggerInterface $logger,
        private UserDeleterRepository $userDeleterRepository,
        private SessionInterface $session,
        private UserPermissionVerifier $userPermissionVerifier,
        private UserActivityLogger $userActivityLogger,
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
        if ($this->userPermissionVerifier->isGrantedToDelete($userIdToDelete)) {
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
