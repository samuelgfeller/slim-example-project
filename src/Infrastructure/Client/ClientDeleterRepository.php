<?php


namespace App\Infrastructure\Client;


use App\Infrastructure\Factory\QueryFactory;

class ClientDeleterRepository

{
    public function __construct(
        private QueryFactory $queryFactory
    ) { }

    /**
     * Delete post from database
     *
     * @param int $id
     * @return bool
     */
    public function deletePost(int $id): bool
    {
        $query = $this->queryFactory->newDelete('post')->where(['id' => $id]);
        return $query->execute()->rowCount() > 0;
    }

    /**
     * Delete post that are linked to user
     *
     * @param int $userId
     * @return bool
     */
    public function deletePostsFromUser(int $userId): bool
    {
        $query = $this->queryFactory->newDelete('post')->where(['user_id' => $userId]);
        return $query->execute()->rowCount() > 0;
    }
}