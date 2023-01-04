<?php

namespace App\Infrastructure\Client;

use App\Common\Hydrator;
use App\Domain\Client\Data\ClientData;
use App\Domain\Client\Data\ClientResultData;
use App\Infrastructure\Factory\QueryFactory;

class ClientFinderRepository
{
    // ClientList select fields are the base (requires the least amount) which can be extended in constructor
    // To prevent duplicate code the client list aggregate select fields are in this array below
    private array $clientListAggregateSelectFields = [
        // Client data retrieved with original name to populate parent class ClientData
        'id' => 'client.id',
        'first_name' => 'client.first_name',
        'last_name' => 'client.last_name',
        'birthdate' => 'client.birthdate',
        'location' => 'client.location',
        'phone' => 'client.phone',
        'email' => 'client.email',
        'sex' => 'client.sex',
        'client_message' => 'client.client_message',
        'vigilance_level' => 'client.vigilance_level',
        'user_id' => 'client.user_id',
        'client_status_id' => 'client.client_status_id',
        'updated_at' => 'client.updated_at',
        'created_at' => 'client.created_at',
        'deleted_at' => 'client.deleted_at',
        // User data prefixed with user_
        'user_first_name' => 'user.first_name',
        'user_surname' => 'user.surname',
        // Client status data prefixed with client_status_
        'client_status_name' => 'client_status.name',
    ];
    // In constructor extended client list select fields for READ
    private array $clientReadAggregateSelectFields;

    public function __construct(
        private readonly QueryFactory $queryFactory,
        private readonly Hydrator $hydrator
    ) {
        $this->clientReadAggregateSelectFields = array_merge($this->clientListAggregateSelectFields, [
            // Main note data prefixed with `note_`
            // Only necessary note fields
            'main_note_id' => 'note.id',
            'note_message' => 'note.message',
            'note_hidden' => 'note.hidden',
            'note_user_id' => 'note.user_id',
            'note_updated_at' => 'note.updated_at',
        ]);
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
     * @return ClientResultData[]
     */
    public function findClientsWithResultAggregate(array $whereArray = ['client.deleted_at IS' => null]): array
    {
        $query = $this->queryFactory->newQuery()->from('client');
        $query->select(
            $this->clientListAggregateSelectFields
        )// Multiple joins doc: https://book.cakephp.org/4/en/orm/query-builder.html#adding-joins
        ->join([
            // `user` is alias and has to be same as $clientAggregateSelectFields (could be `u` as well as long as its consistent)
            'user' => ['table' => 'user', 'type' => 'LEFT', 'conditions' => 'client.user_id = user.id'],
            'client_status' => [
                'table' => 'client_status',
                'type' => 'LEFT',
                'conditions' => 'client.client_status_id = client_status.id',
            ],
        ])
            ->where($whereArray);
        $sql = $query->sql();
        $resultRows = $query->execute()->fetchAll('assoc') ?: [];
        // Convert to list of Post objects with associated User info
        return $this->hydrator->hydrate($resultRows, ClientResultData::class);
    }

    /**
     * Return post with given id if it exists
     * otherwise null.
     *
     * @param string|int $id
     *
     * @return ClientData
     */
    public function findClientById(string|int $id): ClientData
    {
        $query = $this->queryFactory->newQuery()->select(['*'])->from('client')->where(
            ['deleted_at IS' => null, 'id' => $id]
        );
        $postRow = $query->execute()->fetch('assoc') ?: [];

        return new ClientData($postRow);
    }

    /**
     * Return client with relevant aggregate data for client read.
     *
     * @param int $id
     *
     * @return ClientResultData
     */
    public function findClientAggregateByIdIncludingDeleted(int $id): ClientResultData
    {
        $query = $this->queryFactory->newQuery()->from('client');

        $query->select($this->clientReadAggregateSelectFields)
            ->join([
                // `user` is alias and has to be same as $clientAggregateSelectFields (could be `u` as well as long as its consistent)
                'user' => ['table' => 'user', 'type' => 'LEFT', 'conditions' => 'client.user_id = user.id'],
                'client_status' => [
                    'table' => 'client_status',
                    'type' => 'LEFT',
                    'conditions' => 'client.client_status_id = client_status.id',
                ],
                'note' => [
                    'table' => 'note',
                    'type' => 'LEFT',
                    'conditions' => 'client.id = note.client_id AND note.deleted_at IS NULL AND note.is_main = 1',
                ],
            ])
            ->andWhere(
                ['client.id' => $id]
            );

        $resultRows = $query->execute()->fetch('assoc') ?: [];
        // Instantiate UserPost DTO
        return new ClientResultData($resultRows);
    }

    /**
     * Return all posts which are linked to the given user.
     *
     * @param int $userId
     *
     * @return ClientResultData[]
     */
    public function findAllClientsByUserId(int $userId): array
    {
        $query = $this->queryFactory->newQuery()->from('client');

        $query->select(
            $this->clientListAggregateSelectFields
        )->join(['table' => 'user', 'conditions' => 'client.user_id = user.id'])
            ->join(['table' => 'client_status', 'conditions' => 'client.client_status_id = client_status.id'])
            ->andWhere(
                ['client.user_id' => $userId, 'client.deleted_at IS' => null]
            );
        $resultRows = $query->execute()->fetchAll('assoc') ?: [];
        // Convert to list of Post objects with aggregate
        return $this->hydrator->hydrate($resultRows, ClientResultData::class);
    }
}
