<?php

namespace App\Module\UserActivity\List\Repository;

use App\Core\Infrastructure\Database\Hydrator;
use App\Core\Infrastructure\Database\QueryFactory;
use App\Module\UserActivity\Data\UserActivityData;

final readonly class UserActivityListFinderRepository
{
    public function __construct(
        private QueryFactory $queryFactory,
        private Hydrator $hydrator,
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
}
