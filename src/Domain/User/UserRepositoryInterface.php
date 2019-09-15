<?php
declare(strict_types=1);

namespace App\Domain\User;

use App\Infrastructure\Persistence\Exceptions\PersistenceRecordNotFoundException;
use User;


interface UserRepositoryInterface
{
    /**
     * @return User[]
     */
    public function findAllUsers(): array;
    
    /**
     * Return user with given id if it exists
     * otherwise null
     *
     * @param int $id
     * @return array
     */
    public function findUserById(int $id): array;
    
    /**
     * Retrieve user from database
     * If not found error is thrown
     *
     * @param int $id
     * @return array
     * @throws PersistenceRecordNotFoundException
     */
    public function getUserById(int $id): array;
    
    /**
     * Insert user in database
     *
     * @param array $data
     * @return int lastInsertId
     */
    public function insertUser(array $data): string;
    
    /**
     * Delete user from database
     *
     * @param int $id
     * @return bool
     */
    public function deleteUser(int $id): bool;
    
    /**
     * Update values from user
     * Example of $data: ['name' => 'New name']
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateUser(array $data, int $id): bool;
}
