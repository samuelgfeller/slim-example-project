<?php

namespace App\Infrastructure\User;

use App\Infrastructure\Factory\QueryFactory;

class UserCreatorRepository
{
    public function __construct(
        private readonly QueryFactory $queryFactory
    ) {
    }

    /**
     * Insert user in database.
     *
     * @param array $userData
     *
     * @return int lastInsertId
     */
    public function insertUser(array $userData): int
    {
        return (int)$this->queryFactory->insertQueryWithData($userData)->into('user')->execute()->lastInsertId();
    }
}
