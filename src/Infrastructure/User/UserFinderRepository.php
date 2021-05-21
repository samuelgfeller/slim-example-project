<?php


namespace App\Infrastructure\User;


use App\Common\Hydrator;
use App\Domain\User\DTO\User;
use App\Infrastructure\DataManager;
use App\Infrastructure\Exceptions\PersistenceRecordNotFoundException;

class UserFinderRepository
{
    // Fields without password
    private array $fields = ['id', 'name', 'email', 'updated_at', 'created_at'];

    public function __construct(
        private DataManager $dataManager,
        private Hydrator $hydrator
    ) { }

    /**
     * Return all users
     *
     * @return User[]
     */
    public function findAllUsers(): array
    {
        $usersRows = $this->dataManager->findAll('user', $this->fields);
        // Convert to list of objects
        return $this->hydrator->hydrate($usersRows, User::class);
    }

    /**
     * Return user with given id if it exists
     * otherwise null
     *
     * @param string $id
     * @return User
     */
    public function findUserById(string $id): User
    {
        $userRows = $this->dataManager->findById('user', $id);
        // Empty user object if not found
        return new User($userRows);
    }

    /**
     * Return user with given id if it exists
     * If there is no user, an empty object is returned because:
     * > It is considered a best practice to NEVER return null when returning a collection or enumerable
     * Source: https://stackoverflow.com/a/1970001/9013718
     *
     * @param string|null $email
     * @return User
     */
    public function findUserByEmail(?string $email): User
    {
        $userRows = $this->dataManager->findOneBy(
            'user',
            'email',
            $email
        );
        // Empty user object if not found
        return new User($userRows);
    }

    /**
     * Retrieve user from database
     * If not found error is thrown
     *
     * @param int $id
     * @return User
     * @throws PersistenceRecordNotFoundException
     */
    public function getUserById(int $id): User
    {
        $userRows = $this->dataManager->getById('user', $id, $this->fields);
        return new User($userRows);
    }
}