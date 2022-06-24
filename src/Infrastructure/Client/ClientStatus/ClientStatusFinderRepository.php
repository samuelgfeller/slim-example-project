<?php


namespace App\Infrastructure\Client\ClientStatus;

use App\Infrastructure\Factory\QueryFactory;

class ClientStatusFinderRepository
{

    public function __construct(
        private QueryFactory $queryFactory,
    ) {
    }

    /**
     * Return all client statuses with as key the id and value the name
     *
     * @return array{id: string, name: string}
     */
    public function findAllStatusesForDropdown(): array
    {
        $query = $this->queryFactory->newQuery()->from('client_status');

        $query->select(['id', 'name'])
            ->andWhere(
                ['deleted_at IS' => null]
            );
        // Convert to list of Post objects with aggregate
        $resultRows = $query->execute()->fetchAll('assoc') ?: [];
        $statuses = [];
        foreach ($resultRows as $resultRow) {
            $statuses[$resultRow['id']] = $resultRow['name'];
        }
        return $statuses;
    }
}