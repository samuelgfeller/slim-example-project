<?php

namespace App\Module\Authentication\ChangeUserStatus\Service;

use App\Module\Authentication\ChangeUserStatus\Repository\UserStatusUpdaterRepository;
use App\Module\User\Enum\UserStatus;

final readonly class UserStatusUpdater
{
    public function __construct(
        private UserStatusUpdaterRepository $userStatusUpdaterRepository,
    ) {
    }

    /**
     * Change user status.
     *
     * @param UserStatus $status
     * @param string|int|null $userId
     *
     * @return bool
     */
    public function updateStatus(UserStatus $status, string|int|null $userId): bool
    {
        return $this->userStatusUpdaterRepository->changeUserStatus($status, $userId);
    }
}
