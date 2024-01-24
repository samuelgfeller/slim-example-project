<?php

namespace App\Domain\Client\Repository;

use App\Domain\Factory\Infrastructure\QueryFactory;

readonly class ClientUpdaterRepository
{
    public function __construct(
        private QueryFactory $queryFactory
    ) {
    }

    /**
     * Update values from client.
     *
     * @param int $clientId
     * @param array $data ['col_name' => 'New name']
     *
     * @return bool
     */
    public function updateClient(array $data, int $clientId): bool
    {
        $query = $this->queryFactory->updateQuery()->update('client')->set($data)->where(['id' => $clientId]);

        return $query->execute()->rowCount() > 0;
    }

    /**
     * Restore all notes from the client with the same most recent deleted_at date.
     *
     * @param int $clientId
     *
     * @return bool
     */
    public function restoreNotesFromClient(int $clientId): bool
    {
        // Find the most recent deleted_at date for the notes of the given client
        $mostRecentDeletedAt = $this->queryFactory->selectQuery()
            ->select('MAX(deleted_at) as max_deleted_at')
            ->from('note')
            ->where(['client_id' => $clientId])
            ->execute()->fetch('assoc')['max_deleted_at'];

        if (!$mostRecentDeletedAt) {
            // No notes found for the given client
            return false;
        }

        // Restore all notes with the most recent deleted_at date
        $query = $this->queryFactory->updateQuery()
            ->update('note')
            ->set(['deleted_at' => null])
            ->where(['client_id' => $clientId, 'deleted_at' => $mostRecentDeletedAt]);

        return $query->execute()->rowCount() > 0;
    }
}
