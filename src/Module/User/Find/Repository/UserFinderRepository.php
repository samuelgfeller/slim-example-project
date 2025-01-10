<?php

namespace App\Module\User\Find\Repository;

use App\Core\Infrastructure\Database\QueryFactory;

class UserFinderRepository
{
    // Fields without password
    private array $fields = [
        'id',
        'first_name',
        'last_name',
        'email',
        'user_role_id',
        'status',
        'updated_at',
        'created_at',
        'theme',
        'language',
    ];

    public function __construct(
        private readonly QueryFactory $queryFactory,
    ) {
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
        $query = $this->queryFactory->selectQuery()->select($this->fields)->from('user')->where(
            ['deleted_at IS' => null, 'id' => $id]
        );

        // Empty array if not found
        return $query->execute()->fetch('assoc') ?: [];
    }
}
