<?php

namespace App\Infrastructure\Note;

use App\Infrastructure\Factory\QueryFactory;

class NoteDeleterRepository
{
    public function __construct(
        private readonly QueryFactory $queryFactory
    ) {
    }

    /**
     * Delete note from database.
     *
     * @param int $id
     *
     * @return bool
     */
    public function deleteNote(int $id): bool
    {
        $query = $this->queryFactory->newDelete('note')->where(['id' => $id]);

        return $query->execute()->rowCount() > 0;
    }

    /**
     * Delete notes that are linked to client.
     *
     * @param int $clientId
     *
     * @return bool
     */
    public function deleteNotesFromClient(int $clientId): bool
    {
        $query = $this->queryFactory->newDelete('note')->where(['client_id' => $clientId]);

        return $query->execute()->rowCount() > 0;
    }
}
