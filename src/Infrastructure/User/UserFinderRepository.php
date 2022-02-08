<?php


namespace App\Infrastructure\User;


use App\Common\Hydrator;
use App\Domain\User\Data\UserData;
use App\Infrastructure\Factory\QueryFactory;

class UserFinderRepository
{
    // Fields without password
    private array $fields = ['id', 'first_name', 'surname', 'email', 'updated_at', 'created_at'];

    public function __construct(
        private QueryFactory $queryFactory,
        private Hydrator $hydrator
    ) {
    }

    /**
     * Return all users
     *
     * @return UserData[]
     */
    public function findAllUsers(): array
    {
        $query = $this->queryFactory->newQuery()->select($this->fields)->from('user')->where(
            ['deleted_at IS' => null]
        );
        $usersRows = $query->execute()->fetchAll('assoc') ?: [];

        // Convert to list of objects
        return $this->hydrator->hydrate($usersRows, UserData::class);
    }

    /**
     * Return user with given id if it exists
     * otherwise null
     *
     * @param string $id
     * @return UserData
     */
    public function findUserById(string $id): UserData
    {
        $query = $this->queryFactory->newQuery()->select(['*'])->from('user')->where(
            ['deleted_at IS' => null, 'id' => $id]
        );
        $userRows = $query->execute()->fetch('assoc') ?: [];
        // Empty user object if not found
        return new UserData($userRows);
    }

    /**
     * Return user with given id if it exists
     * If there is no user, an empty object is returned because:
     * > It is considered a best practice to NEVER return null when returning a collection or enumerable
     * Source: https://stackoverflow.com/a/1970001/9013718
     *
     * @param string|null $email
     * @return UserData
     */
    public function findUserByEmail(?string $email): UserData
    {
        $query = $this->queryFactory->newQuery()->select(['*'])->from('user')->andWhere(
            ['deleted_at IS' => null, 'email' => $email]
        );

        $userRows = $query->execute()->fetch('assoc') ?: [];

        // Empty user object if not found
        return new UserData($userRows);
    }

    /**
     * Retrieve user from database
     * If not found error is thrown
     *
     * @param int $id
     * @return UserData
     * Throws PersistenceRecordNotFoundException if not found
     */
    public function getUserById(int $id): UserData
    {
        $query = $this->queryFactory->newQuery()->select($this->fields)->from('user')->andWhere(
            ['deleted_at IS' => null, 'id' => $id]
        );

        $userRows = $query->execute()->fetch('assoc');

        return new UserData($userRows);
    }
}