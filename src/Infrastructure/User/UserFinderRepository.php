<?php


namespace App\Infrastructure\User;


use App\Common\Hydrator;
use App\Domain\User\DTO\User;
use App\Infrastructure\Factory\QueryFactory;

class UserFinderRepository
{
    // Fields without password
    private array $fields = ['id', 'name', 'email', 'updated_at', 'created_at'];

    public function __construct(
        private QueryFactory $queryFactory,
        private Hydrator $hydrator
    ) {
    }

    /**
     * Return all users
     *
     * @return User[]
     */
    public function findAllUsers(): array
    {
        $query = $this->queryFactory->newQuery()->select($this->fields)->from('user')->where(
            ['deleted_at IS' => null]
        );
        $usersRows = $query->execute()->fetchAll('assoc') ?: [];

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
        $query = $this->queryFactory->newQuery()->select(['*'])->from('user')->where(
            ['deleted_at IS' => null, 'id' => $id]
        );
        $userRows = $query->execute()->fetch('assoc') ?: [];
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
        $query = $this->queryFactory->newQuery()->select(['*'])->from('user')->andWhere(
            ['deleted_at IS' => null, 'email' => $email]
        );

        $userRows = $query->execute()->fetch('assoc') ?: [];

        // Empty user object if not found
        return new User($userRows);
    }

    /**
     * Retrieve user from database
     * If not found error is thrown
     *
     * @param int $id
     * @return User
     * Throws PersistenceRecordNotFoundException if not found
     */
    public function getUserById(int $id): User
    {
        $query = $this->queryFactory->newQuery()->select($this->fields)->from('user')->andWhere(
            ['deleted_at IS' => null, 'id' => $id]
        );

        $userRows = $query->execute()->fetch('assoc');

        return new User($userRows);
    }
}