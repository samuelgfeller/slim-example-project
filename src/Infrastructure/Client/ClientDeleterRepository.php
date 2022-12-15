<?php

namespace App\Infrastructure\Client;

use App\Infrastructure\Factory\QueryFactory;

class ClientDeleterRepository
{
    public function __construct(
        private readonly QueryFactory $queryFactory
    ) {
    }

    /**
     * Mark client as deleted in database.
     *
     * @param int $id
     *
     * @return bool
     */
    public function deleteClient(int $id): bool
    {
        $query = $this->queryFactory->newDelete('client')->where(['id' => $id]);

        return $query->execute()->rowCount() > 0;
    }

    /**
     * Delete client from database permanently.
     *
     * @param int $id
     *
     * @return bool
     */
    public function hardDeleteClient(int $id): bool
    {
        $query = $this->queryFactory->newQuery()->delete('client')->where(['id' => $id]);

        return $query->execute()->rowCount() > 0;
    }

    /**
     * Delete post that are linked to user.
     *
     * @param int $userId
     *
     * @return bool
     */
    public function deletePostsFromUser(int $userId): bool
    {
        $query = $this->queryFactory->newDelete('post')->where(['user_id' => $userId]);

        return $query->execute()->rowCount() > 0;
    }
}
