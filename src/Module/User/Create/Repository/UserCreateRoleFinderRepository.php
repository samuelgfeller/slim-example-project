<?php

namespace App\Module\User\Create\Repository;

use App\Core\Infrastructure\Database\QueryFactory;

/**
 * Repository for finding user roles for the user create feature.
 */
final readonly class UserCreateRoleFinderRepository
{
    public function __construct(
        private QueryFactory $queryFactory,
    ) {
    }

    /**
     * Find the id of a user role with the given name.
     *
     * @param string $roleName
     *
     * @return int|null
     */
    public function findUserRoleIdByName(string $roleName): ?int
    {
        $query = $this->queryFactory->selectQuery()->from('user_role');

        $query->select(['id'])->where(['name' => $roleName]);
        $resultRow = $query->execute()->fetch('assoc') ?: [];

        return isset($resultRow['id']) ? (int)$resultRow['id'] : null;
    }
}
