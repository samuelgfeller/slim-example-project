<?php

namespace App\Module\Authentication\Validation\Repository;

use App\Core\Infrastructure\Database\QueryFactory;

readonly class UserPasswordHashFinderRepository
{

    public function __construct(
        private QueryFactory $queryFactory,
    ) {
    }

    /**
     * Return user with password hash if it exists
     * otherwise null.
     *
     * @param int $id
     *
     * @return null|string
     */
    public function findPasswordHashFromUserId(int $id): ?string
    {
        $query = $this->queryFactory->selectQuery()->select(['password_hash'])->from('user')->where(
            ['deleted_at IS' => null, 'id' => $id]
        );
        $userValues = $query->execute()->fetch('assoc') ?: [];

        // Empty user object if not found
        // $notRestricted true as values are safe as they come from the database. It's not a user input.
        return $userValues['password_hash'] ?? null;
    }

}
