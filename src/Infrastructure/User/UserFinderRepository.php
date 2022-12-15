<?php

namespace App\Infrastructure\User;

use App\Common\Hydrator;
use App\Domain\User\Data\UserData;
use App\Domain\User\Data\UserResultData;
use App\Infrastructure\Factory\QueryFactory;

class UserFinderRepository
{
    // Fields without password
    private array $fields = [
        'id',
        'first_name',
        'surname',
        'email',
        'user_role_id',
        'status',
        'updated_at',
        'created_at',
    ];

    public function __construct(
        private readonly QueryFactory $queryFactory,
        private readonly Hydrator $hydrator
    ) {
    }

    /**
     * Return all users.
     *
     * @return UserData[]
     */
    public function findAllUsers(): array
    {
        // Convert to list of objects
        return $this->hydrator->hydrate($this->findAllUserRows(), UserData::class);
    }

    /**
     * Return all users with as UserResultData instance.
     *
     * @return UserResultData[]
     */
    public function findAllUsersForList(): array
    {
        return $this->hydrator->hydrate($this->findAllUserRows(), UserResultData::class);
    }

    /**
     * Returns array of user rows.
     *
     * @return array|false
     */
    public function findAllUserRows(): bool|array
    {
        $query = $this->queryFactory->newQuery()->select($this->fields)->from('user')->where(
            ['deleted_at IS' => null]
        );

        return $query->execute()->fetchAll('assoc') ?: [];
    }

    /**
     * Return user with given id if it exists
     * otherwise null.
     *
     * @param int $id
     *
     * @return array user row
     */
    public function findUserById(int $id): array
    {
        $query = $this->queryFactory->newQuery()->select($this->fields)->from('user')->where(
            ['deleted_at IS' => null, 'id' => $id]
        );
        // Empty array if not found
        return $query->execute()->fetch('assoc') ?: [];
    }

    /**
     * Return user with password hash if it exists
     * otherwise null.
     *
     * @param int $id
     *
     * @throws \Exception
     *
     * @return UserData
     */
    public function findUserByIdWithPasswordHash(int $id): UserData
    {
        $query = $this->queryFactory->newQuery()->select(['*'])->from('user')->where(
            ['deleted_at IS' => null, 'id' => $id]
        );
        $userRows = $query->execute()->fetch('assoc') ?: [];
        // Empty user object if not found
        // $notRestricted true as values are safe as they come from the database. It's not a user input.
        return new UserData($userRows);
    }

    /**
     * Return user with given id if it exists
     * If there is no user, an empty object is returned because:
     * > It is considered a best practice to NEVER return null when returning a collection or enumerable
     * Source: https://stackoverflow.com/a/1970001/9013718.
     *
     * @param string|null $email
     *
     * @throws \Exception
     *
     * @return UserData
     */
    public function findUserByEmail(?string $email): UserData
    {
        $query = $this->queryFactory->newQuery()->select(['*'])->from('user')->andWhere(
            ['deleted_at IS' => null, 'email' => $email]
        );

        $userRows = $query->execute()->fetch('assoc') ?: [];

        // Empty user object if not found
        // $notRestricted true as values are safe as they come from the database. It's not a user input.
        return new UserData($userRows);
    }

    /**
     * Retrieve user from database
     * If not found error is thrown.
     *
     * @param int $id
     *
     * @throws \Exception
     *
     * @return UserData
     * Throws PersistenceRecordNotFoundException if not found
     */
    public function getUserById(int $id): UserData
    {
        $query = $this->queryFactory->newQuery()->select($this->fields)->from('user')->andWhere(
            ['deleted_at IS' => null, 'id' => $id]
        );

        $userRows = $query->execute()->fetch('assoc');

        // $notRestricted true as values are safe as they come from the database. It's not a user input.
        return new UserData($userRows);
    }
}
