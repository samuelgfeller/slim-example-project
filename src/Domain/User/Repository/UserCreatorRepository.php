<?php

namespace App\Domain\User\Repository;

use App\Domain\Factory\Infrastructure\QueryFactory;

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