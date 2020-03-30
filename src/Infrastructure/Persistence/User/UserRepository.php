<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\User;

use App\Domain\User\UserNotFoundException;
use App\Infrastructure\Persistence\DataManager;
use App\Infrastructure\Persistence\Exceptions\PersistenceRecordNotFoundException;
use Cake\Database\Connection;

class UserRepository extends DataManager
{
    // Fields without password
    private $fields = ['id','name','email','updated_at','created_at'];

    public function __construct(Connection $conn = null)
    {
        parent::__construct($conn);
        $this->table = 'user';
    }

    /**
     * Return all users
     *
     * @return array
     */
    public function findAllUsers(): array
    {
        return $this->findAll($this->fields);
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
        return $this->findById($id,$this->fields);
    }

    /**
     * Return user with given id if it exists
     * otherwise null
     *
     * @param string|null $email
     * @return array|null
     */
    public function findUserByEmail(?string $email): ?array
    {
        return $this->findOneBy('email', $email,['id','email','password']);
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
        return $this->getById($id,$this->fields);
    }

    /**
     * Insert user in database
     *
     * @param array $data
     * @return string lastInsertId
     */
    public function insertUser(array $data): string {
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
//        return true;
        return $this->update($data, $id);
    }

    /**
     * Retrieve user role
     *
     * @param int $id
     * @return string
     * @throws PersistenceRecordNotFoundException
     */
    public function getUserRole(int $id) : string{
        // todo put role in separate tables
        return $this->getById($id,['role'])['role'];
    }

    /**
     * Retrieve user role
     *
     * @param int $id
     * @return bool
     */
    public function userExists(int $id) : bool{
        return $this->exists('id',$id);
    }



}
