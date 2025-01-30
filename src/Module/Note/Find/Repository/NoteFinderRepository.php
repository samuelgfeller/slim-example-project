<?php

namespace App\Module\Note\Find\Repository;

use App\Infrastructure\Database\QueryFactory;
use App\Module\Note\Data\NoteData;

readonly class NoteFinderRepository
{
    public function __construct(
        private QueryFactory $queryFactory,
    ) {
    }

    /**
     * Return note with given id if it exists
     * otherwise null.
     *
     * @param string|int $id
     *
     * @return NoteData
     */
    public function findNoteById(string|int $id): NoteData
    {
        $query = $this->queryFactory->selectQuery()->select(['*'])->from('note')->where(
            ['deleted_at IS' => null, 'id' => $id]
        );
        $noteRow = $query->execute()->fetch('assoc') ?: [];

        return new NoteData($noteRow);
    }
}
