<?php

namespace App\Module\Client\Create\Repository;

use App\Infrastructure\Database\QueryFactory;

final readonly class ClientCreatorRepository
{
    public function __construct(
        private QueryFactory $queryFactory,
    ) {
    }

    /**
     * Insert client in database.
     *
     * @param array $data keys are the column names
     *
     * @return int lastInsertId
     */
    public function insertClient(array $data): int
    {
        return (int)$this->queryFactory->insertQueryWithData($data)->into('client')->execute()->lastInsertId();
    }
}
