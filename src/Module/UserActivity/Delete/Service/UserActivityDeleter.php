<?php

namespace App\Module\UserActivity\Delete\Service;

use App\Module\UserActivity\Delete\Repository\UserActivityDeleterRepository;

final readonly class UserActivityDeleter
{
    public function __construct(
        private UserActivityDeleterRepository $userActivityDeleterRepository,
    ) {
    }

    /**
     * Delete entry.
     *
     * @param int $activityId
     *
     * @return bool if deleted
     */
    public function deleteUserActivity(int $activityId): bool
    {
        return $this->userActivityDeleterRepository->hardDeleteUserActivity($activityId);
    }
}
