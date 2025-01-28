<?php

namespace App\Module\User\Create\Repository;

use App\Infrastructure\Database\QueryFactory;

final readonly class UserCreatorRepository
{
    public function __construct(
        private QueryFactory $queryFactory,
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
