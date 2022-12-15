<?php

namespace App\Infrastructure\Client;

use App\Infrastructure\Factory\QueryFactory;

class ClientCreatorRepository
{
    public function __construct(
        private readonly QueryFactory $queryFactory
    ) {
    }

    /**
     * Insert client in database.
     *
     * @param array $data key is column name
     *
     * @return int lastInsertId
     */
    public function insertClient(array $data): int
    {
        return (int)$this->queryFactory->newInsert($data)->into('client')->execute()->lastInsertId();
    }
}
