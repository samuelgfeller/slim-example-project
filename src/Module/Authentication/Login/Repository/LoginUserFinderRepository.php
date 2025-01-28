<?php

namespace App\Module\Authentication\Login\Repository;

use App\Infrastructure\Database\QueryFactory;
use App\Module\User\Data\UserData;

readonly class LoginUserFinderRepository
{
    public function __construct(
        private QueryFactory $queryFactory,
    ) {
    }

    /**
     * Return user with given id if it exists
     * If there is no user, an empty object is returned because:
     * > It is considered a best practice to NEVER return null when returning a collection or enumerable
     * Source: https://stackoverflow.com/a/1970001/9013718.
     *
     * @param string|null $email
     *
     * @return UserData
     */
    public function findUserByEmail(?string $email): UserData
    {
        $query = $this->queryFactory->selectQuery()->select(['*'])->from('user')->andWhere(
            ['deleted_at IS' => null, 'email' => $email]
        );

        $userValues = $query->execute()->fetch('assoc') ?: [];

        // Empty user object if not found
        // $notRestricted true as values are safe as they come from the database. It's not a user input.
        return new UserData($userValues);
    }
}
