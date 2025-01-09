<?php

namespace App\Module\Note\Delete\Repository;

use App\Core\Infrastructure\Database\QueryFactory;

final readonly class NoteDeleterRepository
{
    public function __construct(
        private QueryFactory $queryFactory,
    ) {
    }

    /**
     * Soft delete note from database.
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
     * Soft delete notes that are linked to given client.
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
