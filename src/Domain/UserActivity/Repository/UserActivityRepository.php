<?php

namespace App\Domain\UserActivity\Repository;

use App\Common\Hydrator;
use App\Domain\Factory\Infrastructure\QueryFactory;
use App\Domain\User\Data\UserActivityData;

class UserActivityRepository
{
    public function __construct(
        private readonly QueryFactory $queryFactory,
        private readonly Hydrator $hydrator,
    ) {
    }

    /**
     * Return user with given id if it exists
     * otherwise null.
     *
     * @param int|array $userIds
     *
     * @return UserActivityData[]
     */
    public function findUserActivities(int|array $userIds): array
    {
        if ($userIds === []) {
            return [];
        }
        $query = $this->queryFactory->selectQuery()->select('*')->from('user_activity')->where(
            ['user_id IN' => $userIds]
        )->orderByDesc('datetime');
        $resultRows = $query->execute()->fetchAll('assoc') ?: [];

        return $this->hydrator->hydrate($resultRows, UserActivityData::class);
    }

    /**
     * Insert user activity in database.
     *
     * @param mixed $userActivityRow
     *
     * @return int lastInsertId
     */
    public function insertUserActivity($userActivityRow): int
    {
        return (int)$this->queryFactory->insertQueryWithData($userActivityRow)->into('user_activity')->execute()->lastInsertId();
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
