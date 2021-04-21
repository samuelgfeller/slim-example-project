<?php

declare(strict_types=1);

namespace App\Infrastructure\Post;

use App\Common\Hydrator;
use App\Infrastructure\DataManager;
use App\Infrastructure\Exceptions\PersistenceRecordNotFoundException;

class PostRepository
{

    public function __construct(private DataManager $dataManager, private Hydrator $hydrator)
    {
    }

    /**
     * Return all posts
     *
     * @return array
     */
    public function findAllPosts(): array
    {
        return $this->dataManager->findAll('post');
    }

    /**
     * Return post with given id if it exists
     * otherwise null
     *
     * @param string|int $id
     * @return array
     */
    public function findPostById(string|int $id): array
    {
        return $this->dataManager->findById('post', $id);
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
        return $this->dataManager->getById('post', $id);
    }

    /**
     * Return all posts which are linked to the given user
     *
     * @param $userId
     * @return array
     */
    public function findAllPostsByUserId(int $userId): array
    {
        return $this->dataManager->findAllBy('post', 'user_id', $userId);
    }

    /**
     * Insert post in database
     *
     * @param array $data key is column name
     * @return int lastInsertId
     */
    public function insertPost(array $data): int
    {
        return (int)$this->dataManager->newInsert($data)->into('post')->execute()->lastInsertId();
    }

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

    /**
     * Update values from post
     *
     * @param int $id
     * @param array $data ['col_name' => 'New name']
     * @return bool
     */
    public function updatePost(array $data, int $id): bool
    {
        $query = $this->dataManager->newQuery()->update('post')->set($data)->where(['id' => $id]);
        return $query->execute()->rowCount() > 0;
    }
}
