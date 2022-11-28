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
        'created_at'
    ];

    public function __construct(
        private readonly QueryFactory $queryFactory,
        private readonly Hydrator $hydrator,
    ) {
    }

    /**
     * Return user with given id if it exists
     * otherwise null
     *
     * @param int $userId
     * @return UserActivityData[]
     */
    public function findUserActivities(int $userId): array
    {
        $query = $this->queryFactory->newQuery()->select('*')->from('user_activity')->where(
            ['user_id' => $userId]
        );
        $resultRows = $query->execute()->fetchAll('assoc') ?: [];
        return $this->hydrator->hydrate($resultRows, UserActivityData::class);
    }


    /**
     * Insert user activity in database
     *
     * @return int lastInsertId
     */
    public function insertUserActivity($userActivityRow): int
    {
        return (int)$this->queryFactory->newInsert($userActivityRow)->into('user_activity')->execute()->lastInsertId();
    }

    /**
     * Delete user activity entry
     *
     * @param int $activityId
     * @return bool if deletion was successful
     */
    public function deleteUserActivity(int $activityId): bool
    {
        $query = $this->queryFactory->newDelete('user_activity')->where(['id' => $activityId]);
        return $query->execute()->rowCount() > 0;
    }
}