<?php

namespace App\Domain\Note\Repository;

use App\Domain\Factory\Infrastructure\QueryFactory;

class NoteCreatorRepository
{
    public function __construct(
        private readonly QueryFactory $queryFactory
    ) {
    }

    /**
     * Insert note in database.
     *
     * @param array $data key is column name
     *
     * @return int lastInsertId
     */
    public function insertNote(array $data): int
    {
        return (int)$this->queryFactory->insertQueryWithData($data)->into('note')->execute()->lastInsertId();
    }
}
