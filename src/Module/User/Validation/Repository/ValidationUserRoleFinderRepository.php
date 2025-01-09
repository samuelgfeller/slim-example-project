<?php

namespace App\Module\User\Validation\Repository;

use App\Core\Infrastructure\Database\QueryFactory;

final readonly class ValidationUserRoleFinderRepository
{
    public function __construct(
        private QueryFactory $queryFactory,
    ) {
    }

    /**
     * Check if user role with given id exists.
     *
     * @param int|string $userRoleId
     *
     * @return bool
     */
    public function userRoleWithIdExists(string|int $userRoleId): bool
    {
        $query = $this->queryFactory->selectQuery()->from('user_role')->select(['id'])->where(['id' => $userRoleId]);

        return $query->execute()->fetch('assoc') !== false;
    }
}
