<?php
declare(strict_types=1);

namespace App\Domain\Post;

use App\Infrastructure\Persistence\Exceptions\PersistenceRecordNotFoundException;



interface PostRepositoryInterface
{
    /**
     * @return Post[]
     */
    public function findAllPosts(): array;
    
    /**
     * Return user with given id if it exists
     * otherwise null
     *
     * @param int $id
     * @return array
     */
    public function findPostById(int $id): array;

    /**
     * Return user with given email if it exists
     * otherwise null
     *
     * @param string $email
     * @return array
     */
    public function findPostByEmail(string $email): array;
    
    /**
     * Retrieve user from database
     * If not found error is thrown
     *
     * @param int $id
     * @return array
     * @throws PersistenceRecordNotFoundException
     */
    public function getPostById(int $id): array;
    
    /**
     * Insert user in database
     *
     * @param array $data
     * @return string lastInsertId
     */
    public function insertPost(array $data): string;
    
    /**
     * Delete user from database
     *
     * @param int $id
     * @return bool
     */
    public function deletePost(int $id): bool;
    
    /**
     * Update values from user
     * Example of $data: ['name' => 'New name']
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updatePost(array $data, int $id): bool;


}
