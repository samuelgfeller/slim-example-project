<?php

namespace App\Domain\Note\Repository;

use App\Domain\Factory\Infrastructure\QueryFactory;

readonly class NoteDeleterRepository
{
    public function __construct(
        private QueryFactory $queryFactory
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
        $query = $this->queryFactory->softDeleteQuery('note')->where(['id' => $id]);

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
        $query = $this->queryFactory->softDeleteQuery('note')
            ->where(['client_id' => $clientId, 'deleted_at IS' => null]);

        return $query->execute()->rowCount() > 0;
    }
}
