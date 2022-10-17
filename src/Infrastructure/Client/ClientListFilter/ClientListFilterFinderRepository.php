<?php


namespace App\Infrastructure\Client\ClientListFilter;

use App\Infrastructure\Factory\QueryFactory;

class ClientListFilterFinderRepository
{

    public function __construct(
        private QueryFactory $queryFactory,
    ) {
    }

    /**
     * Return all client list filters with as key the id and value the name
     *
     * @return array{id: string, name: string}
     */
    public function findAllClientListFilters(): array
    {
        $query = $this->queryFactory->newQuery()->from('client_list_filter');

        $query->select(['id', 'name']);
        // ->andWhere(['deleted_at IS' => null]);
        $resultRows = $query->execute()->fetchAll('assoc') ?: [];
        $statuses = [];
        foreach ($resultRows as $resultRow) {
            $statuses[(int)$resultRow['id']] = $resultRow['name'];
        }
        return $statuses;
    }
}