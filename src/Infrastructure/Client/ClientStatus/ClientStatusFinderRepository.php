<?php


namespace App\Infrastructure\Client\ClientStatus;

use App\Infrastructure\Factory\QueryFactory;

class ClientStatusFinderRepository
{

    public function __construct(
        private readonly QueryFactory $queryFactory,
    ) {
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