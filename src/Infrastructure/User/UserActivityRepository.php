<?php

namespace App\Infrastructure\User;

use App\Common\Hydrator;
use App\Domain\User\Data\UserActivityData;
use App\Infrastructure\Factory\QueryFactory;

class UserActivityRepository
{
    // Fields without password
    private array $fields = [
        'id',
        'first_name',
        'surname',
        'email',
        'user_role_id',
        'status',
        'updated_at',
        'created_at',
    ];

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
        $query = $this->queryFactory->newQuery()->select('*')->from('user_activity')->where(
            ['user_id IN' => $userIds]
        )->orderDesc('datetime');
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
        return (int)$this->queryFactory->newInsert($userActivityRow)->into('user_activity')->execute()->lastInsertId();
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
        $query = $this->queryFactory->newQuery()->delete('user_activity')->where(['id' => $activityId]);

        return $query->execute()->rowCount() > 0;
    }
}
