<?php

namespace App\Module\Client\DropdownFinder\Repository;

use App\Core\Infrastructure\Database\Hydrator;
use App\Core\Infrastructure\Database\QueryFactory;
use App\Module\User\Data\UserResultData;

class ClientUserDropdownFinderRepository
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
     * Returns array of user rows.
     *
     * @return array
     */
    public function findAllUsers(): array
    {
        $query = $this->queryFactory->selectQuery()->select($this->fields)->from('user')->where(
            ['deleted_at IS' => null]
        );

        $result = $query->execute()->fetchAll('assoc') ?: [];

        return $this->hydrator->hydrate($result, UserResultData::class);
    }
}
