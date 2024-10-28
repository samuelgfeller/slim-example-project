<?php

namespace App\Domain\Client\Repository;

use App\Domain\Client\Data\ClientData;
use App\Domain\Client\Data\ClientListResult;
use App\Domain\Client\Data\ClientReadResult;
use App\Infrastructure\Factory\QueryFactory;
use App\Infrastructure\Utility\Hydrator;

final readonly class ClientFinderRepository
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

    /**
     * Return client with given id if it exists
     * otherwise null.
     *
     * @param string|int $id
     * @param bool $includeDeleted
     *
     * @return ClientData
     */
    public function findClientById(string|int $id, bool $includeDeleted = false): ClientData
    {
        $query = $this->queryFactory->selectQuery()->select(['*'])->from('client')->where(
            ['id' => $id]
        );
        if ($includeDeleted === false) {
            $query->andWhere(['deleted_at IS' => null]);
        }
        $clientRow = $query->execute()->fetch('assoc') ?: [];

        return new ClientData($clientRow);
    }

    /**
     * Return a client with relevant aggregate data for client read.
     *
     * @param int $id
     *
     * @return ClientReadResult
     */
    public function findClientAggregateByIdIncludingDeleted(int $id): ClientReadResult
    {
        $query = $this->queryFactory->selectQuery()->from('client');

        $query->select([
            // Client select fields to populate ClientReadResult DTO and parent ClientData
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
            'user_last_name' => 'user.last_name',
            // Client status data prefixed with client_status_
            'client_status_name' => 'client_status.name',
            // Main note data loaded in page renderer prefixed with `note_`
            'main_note_id' => 'note.id',
            'note_message' => 'note.message',
            'note_hidden' => 'note.hidden',
            'note_user_id' => 'note.user_id',
            'note_updated_at' => 'note.updated_at',
        ])
            ->join([
                'user' => ['table' => 'user', 'type' => 'LEFT', 'conditions' => 'client.user_id = user.id'],
                'client_status' => [
                    'table' => 'client_status',
                    'type' => 'LEFT',
                    'conditions' => 'client.client_status_id = client_status.id',
                ],
                'note' => [
                    'table' => 'note',
                    'type' => 'LEFT',
                    // If there is a deleted main note and a non-deleted one, it creates a conflict,
                    // but this should not happen as there is no way to delete the main note.
                    'conditions' => 'client.id = note.client_id AND note.is_main = 1',
                ],
            ])
            ->andWhere(
                ['client.id' => $id]
            );

        $resultRows = $query->execute()->fetch('assoc') ?: [];

        // Instantiate ClientReadResult DTO
        return new ClientReadResult($resultRows);
    }
}
