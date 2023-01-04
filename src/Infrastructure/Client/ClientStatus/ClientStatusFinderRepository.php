<?php

namespace App\Infrastructure\Client\ClientStatus;

use App\Domain\Client\Enum\ClientStatus;
use App\Infrastructure\Factory\QueryFactory;

class ClientStatusFinderRepository
{
    public function __construct(
        private readonly QueryFactory $queryFactory,
    ) {
    }

    /**
     * Returns client status id of given status enum case.
     *
     * @param ClientStatus $clientStatus
     *
     * @return int|null
     */
    public function findClientStatusByName(ClientStatus $clientStatus): ?int
    {
        $query = $this->queryFactory->newQuery()->from('client_status');

        $query->select(['id', 'name'])
            ->where(
                ['name' => $clientStatus->value],
                ['deleted_at IS' => null]
            );
        $resultRow = $query->execute()->fetch('assoc') ?: [];

        return $resultRow['id'] ?? null;
    }

    /**
     * Return all client statuses with as key the id and value the name.
     * Used for dropdowns.
     *
     * @return array{id: string, name: string}
     */
    public function findAllClientStatusesMappedByIdName(): array
    {
        $query = $this->queryFactory->newQuery()->from('client_status');

        $query->select(['id', 'name'])
            ->andWhere(
                ['deleted_at IS' => null]
            );
        $resultRows = $query->execute()->fetchAll('assoc') ?: [];
        $statuses = [];
        foreach ($resultRows as $resultRow) {
            $statuses[(int)$resultRow['id']] = $resultRow['name'];
        }

        return $statuses;
    }
}
