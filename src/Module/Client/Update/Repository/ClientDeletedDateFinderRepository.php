<?php

namespace App\Module\Client\Update\Repository;

use App\Infrastructure\Database\QueryFactory;

/**
 * When a client is restored, the notes with the same deleted date as the client are restored.
 */
readonly class ClientDeletedDateFinderRepository
{
    public function __construct(
        private QueryFactory $queryFactory,
    ) {
    }

    public function findClientDeletedAtDate(string|int $id): ?\DateTimeImmutable
    {
        $query = $this->queryFactory->selectQuery()->select(['deleted_at'])->from('client')->where(
            ['id' => $id]
        );

        $deletedAt = $query->execute()->fetch('assoc')['deleted_at'] ?? null;

        return $deletedAt !== null ? new \DateTimeImmutable($deletedAt) : null;
    }
}
