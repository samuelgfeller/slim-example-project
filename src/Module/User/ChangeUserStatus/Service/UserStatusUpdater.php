<?php

namespace App\Module\User\ChangeUserStatus\Service;

use App\Module\User\ChangeUserStatus\Repository\UserStatusUpdaterRepository;
use App\Module\User\Enum\UserStatus;

readonly class UserStatusUpdater
{
    public function __construct(
        private UserStatusUpdaterRepository $userStatusUpdaterRepository
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
