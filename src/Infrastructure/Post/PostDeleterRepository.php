<?php


namespace App\Infrastructure\Post;


use App\Infrastructure\DataManager;

class PostDeleterRepository

{
    public function __construct(
        private DataManager $dataManager
    ) { }

    /**
     * Delete post from database
     *
     * @param int $id
     * @return bool
     */
    public function deletePost(int $id): bool
    {
        $query = $this->dataManager->newDelete('post')->where(['id' => $id]);
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
        $query = $this->dataManager->newDelete('post')->where(['user_id' => $userId]);
        return $query->execute()->rowCount() > 0;
    }
}