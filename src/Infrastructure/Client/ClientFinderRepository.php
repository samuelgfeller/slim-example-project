<?php


namespace App\Infrastructure\Client;


use App\Common\Hydrator;
use App\Domain\Client\Data\ClientData;
use App\Domain\Client\Data\ClientResultAggregateData;
use App\Infrastructure\Exceptions\PersistenceRecordNotFoundException;
use App\Infrastructure\Factory\QueryFactory;

class ClientFinderRepository
{

    public function __construct(
        private QueryFactory $queryFactory,
        private Hydrator     $hydrator
    )
    {
    }

    /**
     * Return all clients with some aggregate data (user and status) attribute loaded that makes
     * sense for client result. Typically, things that will be used by the frontend when
     * displaying clients.
     *
     * Side note: difference between Association, aggregation / composition and inheritance
     * https://www.visual-paradigm.com/guide/uml-unified-modeling-language/uml-aggregation-vs-composition/
     *
     * @return ClientData[]
     */
    public function findAllClientsWithResultAggregate(): array
    {
        $query = $this->queryFactory->newQuery()->from('post');
        $query->select(
            [
                // Client data retrieved with original name to populate ClientData
                'id' => 'client.id',
                'first_name' => 'client.first_name',
                'last_name' => 'client.last_name',
                'birthdate' => 'client.birthdate',
                'location' => 'client.location',
                'phone' => 'client.phone',
                'email' => 'client.email',
                'note' => 'client.note',
                'user_id' => 'client.user_id',
                'client_status_id' => 'client.client_status_id',
                'updated_at' => 'client.updated_at',
                'created_at' => 'client.created_at',
                // User data prefixed with user_
                'user_first_name' => 'user.first_name',
                'user_surname' => 'user.surname',
                // Client status data prefixed with client_status_
                'client_status_name' => 'client_status.name',
            ]
        )->join(['table' => 'user', 'conditions' => 'client.user_id = user.id'])
            ->join(['table' => 'client_status', 'conditions' => 'client.client_status_id = client_status.id'])
            ->andWhere(
            ['client.deleted_at IS' => null]
        );
        $resultRows = $query->execute()->fetchAll('assoc') ?: [];
        // Convert to list of Post objects with associated User info
        return $this->hydrator->hydrate($resultRows, ClientResultAggregateData::class);
    }

    /**
     * Return post with given id if it exists
     * otherwise null
     *
     * @param string|int $id
     * @return ClientData
     */
    public function findPostById(string|int $id): ClientData
    {
        $query = $this->queryFactory->newQuery()->select(['*'])->from('post')->where(
            ['deleted_at IS' => null, 'id' => $id]
        );
        $postRow = $query->execute()->fetch('assoc') ?: [];
        return new ClientData($postRow);
    }

    /**
     * Return all posts with users attribute loaded
     *
     * @param int $id
     * @return UserPostData
     */
    public function findUserPostById(int $id): UserPostData
    {
        $query = $this->queryFactory->newQuery()->from('post');

        $concatName = $query->func()->concat(['user.first_name' => 'identifier', ' ', 'user.surname' => 'identifier']);

        $query->select(
            [
                'post_id' => 'post.id',
                'user_id' => 'user.id',
                'post_message' => 'post.message',
                'post_created_at' => 'post.created_at',
                'post_updated_at' => 'post.updated_at',
                'user_name' => $concatName,
                'user_role' => 'user.role',
            ]
        )->join(['table' => 'user', 'conditions' => 'post.user_id = user.id'])->andWhere(
            ['post.id' => $id, 'post.deleted_at IS' => null]
        );
        $resultRows = $query->execute()->fetch('assoc') ?: [];
        // Instantiate UserPost DTO
        return new UserPostData($resultRows);
    }


    /**
     * Retrieve post from database
     * If not found error is thrown
     *
     * @param int $id
     * @return array
     * @throws PersistenceRecordNotFoundException
     */
    public function getPostById(int $id): array
    {
        $query = $this->queryFactory->newQuery()->select(['*'])->from('post')->where(
            ['deleted_at IS' => null, 'id' => $id]
        );
        $entry = $query->execute()->fetch('assoc');
        if (!$entry) {
            throw new PersistenceRecordNotFoundException('post');
        }
        return $entry;
    }

    /**
     * Return all posts which are linked to the given user
     *
     * @param int $userId
     * @return UserPostData[]
     */
    public function findAllPostsByUserId(int $userId): array
    {
        $query = $this->queryFactory->newQuery()->from('post');

        $concatName = $query->func()->concat(['user.first_name' => 'identifier', ' ', 'user.surname' => 'identifier']);

        $query->select(
            [
                'post_id' => 'post.id',
                'user_id' => 'user.id',
                'post_message' => 'post.message',
                'post_created_at' => 'post.created_at',
                'post_updated_at' => 'post.updated_at',
                'user_name' => $concatName,
                'user_role' => 'user.role',
            ]
        )->join(['table' => 'user', 'conditions' => 'post.user_id = user.id'])->andWhere(
            [
                'post.user_id' => $userId, // Not unsafe as its not an expression and thus escaped by querybuilder
                'post.deleted_at IS' => null
            ]
        );
        $resultRows = $query->execute()->fetchAll('assoc') ?: [];
        // Convert to list of Post objects with associated User info
        return $this->hydrator->hydrate($resultRows, UserPostData::class);
    }
}