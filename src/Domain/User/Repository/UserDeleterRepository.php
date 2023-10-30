<?php

namespace App\Domain\User\Repository;

use App\Domain\Factory\Infrastructure\QueryFactory;

class UserDeleterRepository
{
    public function __construct(
        private readonly QueryFactory $queryFactory
    ) {
    }

    /**
     * Delete user from database.
     *
     * @param int $userId
     *
     * @return bool
     */
    public function deleteUserById(int $userId): bool
    {
        $query = $this->queryFactory->softDeleteQuery('user')->where(['id' => $userId]);

        return $query->execute()->rowCount() > 0;
    }
}
