<?php

namespace App\Module\Client\Update\Repository;

use App\Infrastructure\Database\QueryFactory;

final readonly class ClientUpdaterRepository
{
    public function __construct(
        private QueryFactory $queryFactory,
    ) {
    }

    /**
     * Update client row values.
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
     * @param \DateTimeImmutable|null $clientDeletedAt
     *
     * @return bool
     */
    public function restoreNotesFromClient(int $clientId, ?\DateTimeImmutable $clientDeletedAt): bool
    {
        if ($clientDeletedAt === null) {
            return false;
        }

        // Add and subtract 2 seconds from the client's deleted_at timestamp to add a buffer if client
        // and notes were not deleted at the exact same time in the code.
        $deletedAtPlus2Secs = $clientDeletedAt->modify('+2 seconds')->format('Y-m-d H:i:s');
        $deletedAtMinus2Secs = $clientDeletedAt->modify('-2 seconds')->format('Y-m-d H:i:s');

        // Restore all notes with the deleted_at date within the range of client's deleted_at +/- 2 seconds
        $query = $this->queryFactory->updateQuery()
            ->update('note')
            ->set(['deleted_at' => null])
            ->where(['client_id' => $clientId])
            ->andWhere('deleted_at BETWEEN :deletedAtMinus2Secs AND :deletedAtPlus2Secs')
            ->bind(':deletedAtMinus2Secs', $deletedAtMinus2Secs, 'string')
            ->bind(':deletedAtPlus2Secs', $deletedAtPlus2Secs, 'string');

        return $query->execute()->rowCount() > 0;
    }
}
