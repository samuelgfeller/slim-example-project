<?php

namespace App\Domain\Client\Repository;

use App\Domain\Factory\Infrastructure\QueryFactory;

readonly class ClientCreatorRepository
{
    public function __construct(
        private QueryFactory $queryFactory
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
        return (int)$this->queryFactory->insertQueryWithData($data)->into('client')->execute()->lastInsertId();
    }
}
