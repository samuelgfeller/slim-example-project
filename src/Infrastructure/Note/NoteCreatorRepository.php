<?php

namespace App\Infrastructure\Note;

use App\Infrastructure\Factory\QueryFactory;

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
        return (int)$this->queryFactory->newInsert($data)->into('note')->execute()->lastInsertId();
    }
}
