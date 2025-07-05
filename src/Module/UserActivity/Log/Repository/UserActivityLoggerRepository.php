<?php

namespace App\Module\UserActivity\Log\Repository;

use App\Infrastructure\Database\QueryFactory;

final readonly class UserActivityLoggerRepository
{
    public function __construct(
        private QueryFactory $queryFactory,
    ) {
    }

    /**
     * Insert user activity in database.
     *
     * @param array $userActivityRow
     *
     * @return int lastInsertId
     */
    public function insertUserActivity(array $userActivityRow): int
    {
        return (int)$this->queryFactory->insertQueryWithData($userActivityRow)->into('user_activity')->execute()->lastInsertId();
    }
}
