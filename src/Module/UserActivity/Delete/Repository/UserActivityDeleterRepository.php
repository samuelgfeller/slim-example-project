<?php

namespace App\Module\UserActivity\Delete\Repository;

use App\Core\Infrastructure\Database\QueryFactory;

final readonly class UserActivityDeleterRepository
{
    public function __construct(
        private QueryFactory $queryFactory,
    ) {
    }

    /**
     * Delete user activity entry.
     *
     * @param int $activityId
     *
     * @return bool if deletion was successful
     */
    public function hardDeleteUserActivity(int $activityId): bool
    {
        $query = $this->queryFactory->hardDeleteQuery()->delete('user_activity')->where(['id' => $activityId]);

        return $query->execute()->rowCount() > 0;
    }
}
