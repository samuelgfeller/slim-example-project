<?php

namespace App\Module\Client\ClientStatus\Repository;

use App\Infrastructure\Database\QueryFactory;
use App\Module\Client\ClientStatus\Enum\ClientStatus;

final readonly class ClientStatusFinderRepository
{
    public function __construct(
        private QueryFactory $queryFactory,
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
        $query = $this->queryFactory->selectQuery()->from('client_status');

        $query->select(['id', 'name'])->where(['name' => $clientStatus->value, 'deleted_at IS' => null]);
        $resultRow = $query->execute()->fetch('assoc') ?: [];

        return $resultRow['id'] ?? null;
    }

    /**
     * Return all client statuses with as key the id and value the name.
     * Used for dropdowns.
     *
     * @param bool $withoutTranslation
     *
     * @return array<int, string>
     */
    public function findAllClientStatusesMappedByIdName(bool $withoutTranslation = false): array
    {
        $query = $this->queryFactory->selectQuery()->from('client_status');

        $query->select(['id', 'name'])->andWhere(['deleted_at IS' => null]);

        $resultRows = $query->execute()->fetchAll('assoc') ?: [];
        $statuses = [];
        foreach ($resultRows as $resultRow) {
            // If status is required without the translation, provide value directly from db
            // Translation is made in ClientStatus enum
            $statusName = $withoutTranslation ? $resultRow['name']
                : ClientStatus::tryFrom($resultRow['name'])?->getDisplayName();
            $statuses[(int)$resultRow['id']] = $statusName;
        }

        return $statuses;
    }
}
