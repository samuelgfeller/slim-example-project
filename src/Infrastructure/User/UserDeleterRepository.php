<?php

namespace App\Infrastructure\User;

use App\Infrastructure\Factory\QueryFactory;

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
        $query = $this->queryFactory->newDelete('user')->where(['id' => $userId]);

        return $query->execute()->rowCount() > 0;
    }
}
