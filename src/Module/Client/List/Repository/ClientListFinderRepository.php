<?php

namespace App\Module\Client\List\Repository;

use App\Core\Infrastructure\Factory\QueryFactory;
use App\Core\Infrastructure\Utility\Hydrator;
use App\Module\Client\List\Data\ClientListResult;

final readonly class ClientListFinderRepository
{
    public function __construct(
        private QueryFactory $queryFactory,
        private Hydrator $hydrator,
    ) {
    }

    /**
     * Return all clients with some aggregate data (user and status) attribute loaded that makes
     * sense for client result. Typically, things that will be used by the frontend when
     * displaying clients.
     *
     * Side note: difference between Association, aggregation / composition and inheritance
     * https://www.visual-paradigm.com/guide/uml-unified-modeling-language/uml-aggregation-vs-composition/
     *
     * @param array $whereArray
     *
     * @return ClientListResult[]
     */
    public function findClientsWithResultAggregate(array $whereArray = ['client.deleted_at IS' => null]): array
    {
        $query = $this->queryFactory->selectQuery()->from('client');
        $query->select([
            'id' => 'client.id',
            'first_name' => 'client.first_name',
            'last_name' => 'client.last_name',
            'birthdate' => 'client.birthdate',
            'email' => 'client.email',
            'location' => 'client.location',
            'phone' => 'client.phone',
            'sex' => 'client.sex',
            'user_id' => 'client.user_id',
            'client_status_id' => 'client.client_status_id',
            'deleted_at' => 'client.deleted_at',
            // User data prefixed with user_
            'user_first_name' => 'user.first_name',
            'user_last_name' => 'user.last_name',
            // Client status data prefixed with client_status_
            'client_status_name' => 'client_status.name',
        ])// Multiple joins doc: https://book.cakephp.org/4/en/orm/query-builder.html#adding-joins
        ->join([
            'user' => ['table' => 'user', 'type' => 'LEFT', 'conditions' => 'client.user_id = user.id'],
            'client_status' => [
                'table' => 'client_status',
                'type' => 'LEFT',
                'conditions' => 'client.client_status_id = client_status.id',
            ],
        ])
            ->where($whereArray);
        $resultRows = $query->execute()->fetchAll('assoc') ?: [];

        // Create instances of ClientListResult objects with associated User info
        return $this->hydrator->hydrate($resultRows, ClientListResult::class);
    }
}
