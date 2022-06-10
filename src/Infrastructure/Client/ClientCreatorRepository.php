<?php


namespace App\Infrastructure\Client;

use App\Infrastructure\Factory\QueryFactory;

class ClientCreatorRepository

{
    public function __construct(
        private QueryFactory $queryFactory
    )
    {
    }

    /**
     * Insert post in database
     *
     * @param array $data key is column name
     * @return int lastInsertId
     */
    public function insertPost(array $data): int
    {
        return (int)$this->queryFactory->newInsert($data)->into('post')->execute()->lastInsertId();
    }
}