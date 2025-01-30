<?php

namespace App\Module\User\FindDropdownOptions\Repository;

use App\Infrastructure\Database\QueryFactory;
use App\Module\User\Enum\UserRole;

final readonly class UserDropdownOptionsRoleFinderRepository
{
    public function __construct(
        private QueryFactory $queryFactory,
    ) {
    }

    /**
     * Return all user roles with the key being the id and value the name.
     *
     * @return array<int, string>
     */
    public function findAllUserRolesForDropdown(): array
    {
        $query = $this->queryFactory->selectQuery()->from('user_role');

        $query->select(['id', 'name']);
        $resultRows = $query->execute()->fetchAll('assoc') ?: [];
        $userRoles = [];
        foreach ($resultRows as $resultRow) {
            $userRoles[(int)$resultRow['id']] = UserRole::from($resultRow['name'])->getDisplayName();
        }

        return $userRoles;
    }
}
