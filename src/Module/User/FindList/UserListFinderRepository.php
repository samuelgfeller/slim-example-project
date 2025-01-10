<?php

namespace App\Module\User\FindList;

use App\Core\Infrastructure\Database\Hydrator;
use App\Core\Infrastructure\Database\QueryFactory;
use App\Module\User\Data\UserData;

class UserListFinderRepository
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
        private readonly Hydrator $hydrator,
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
     * Returns array of user rows.
     *
     * @return array
     */
    public function findAllUserRows(): array
    {
        $query = $this->queryFactory->selectQuery()->select($this->fields)->from('user')->where(
            ['deleted_at IS' => null]
        );

        return $query->execute()->fetchAll('assoc') ?: [];
    }
}
