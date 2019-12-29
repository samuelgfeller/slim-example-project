<?php
declare(strict_types=1);

namespace App\Domain\Post;

use App\Infrastructure\Persistence\Exceptions\PersistenceRecordNotFoundException;



interface PostRepositoryInterface
{
    /**
     * @return array[]
     */
    public function findAllPosts(): array;
    /**
     * Return post with given id if it exists
     * otherwise null
     *
     * @param int $id
     * @return array
     */
    public function findPostById(int $id): array;

    
    /**
     * Retrieve post from database
     * If not found error is thrown
     *
     * @param int $id
     * @return array
     * @throws PersistenceRecordNotFoundException
     */
    public function getPostById(int $id): array;

    /**
     * Return all posts which are linked to the given user
     *
     * @param int $userId
     * @return array
     */
    public function findAllPostsByUserId(int $userId): array;

    
    /**
     * Insert post in database
     *
     * @param array $data
     * @return string lastInsertId
     */
    public function insertPost(array $data): string;
    
    /**
     * Delete post from database
     *
     * @param int $id
     * @return bool
     */
    public function deletePost(int $id): bool;
    
    /**
     * Update values from post
     * Example of $data: ['name' => 'New name']
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updatePost(array $data, int $id): bool;


}
