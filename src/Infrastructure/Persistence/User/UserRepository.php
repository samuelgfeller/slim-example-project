<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\User;

use App\Domain\User\UserNotFoundException;
use App\Domain\User\UserRepositoryInterface;
use App\Infrastructure\Persistence\DataManager;
use App\Infrastructure\Persistence\Exceptions\PersistenceRecordNotFoundException;
use Cake\Database\Connection;
use User;

class UserRepository extends DataManager implements UserRepositoryInterface
{

    public function __construct(Connection $conn = null)
    {
        parent::__construct($conn);
        $this->table = 'user';
    }
    

    public function findAllUsers(): array
    {
        return $this->findAll();
    }
    
    /**
     * Return user with given id if it exists
     * otherwise null
     *
     * @param int $id
     * @return array
     */
    public function findUserById(int $id): array
    {
        return $this->findById($id);
    }
    
    /**
     * Retrieve user from database
     * If not found error is thrown
     *
     * @param int $id
     * @return array
     * @throws PersistenceRecordNotFoundException
     */
    public function getUserById(int $id): array
    {
        return $this->getById($id);
    }
    
    /**
     * Insert user in database
     *
     * @param array $data
     * @return int lastInsertId
     */
    public function insertUser(array $data): int {
        return $this->insert($data);
    }
    
    /**
     * Delete user from database
     *
     * @param int $id
     * @return bool
     */
    public function deleteUser(int $id): bool {
        return $this->delete($id);
    }
    
    /**
     * Update values from user
     * Example of $data: ['name' => 'New name']
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateUser(array $data,int $id): bool {
        return $this->update($data,$id);
    }
}
