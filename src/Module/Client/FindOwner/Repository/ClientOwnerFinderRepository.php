<?php

namespace App\Module\Client\FindOwner\Repository;

use App\Infrastructure\Database\QueryFactory;

final readonly class ClientOwnerFinderRepository
{
    public function __construct(
        private QueryFactory $queryFactory,
    ) {
    }

    /**
     * Return client with given id if it exists
     * otherwise null.
     *
     * @param string|int $id
     *
     * @return int|null
     */
    public function findClientOwnerId(string|int $id): ?int
    {
        $query = $this->queryFactory->selectQuery()->select(['user_id'])->from('client')->where(
            ['id' => $id]
        );

        $userId = $query->execute()->fetch('assoc')['user_id'] ?? null;

        return $userId !== null ? (int)$userId : null;
    }
}
