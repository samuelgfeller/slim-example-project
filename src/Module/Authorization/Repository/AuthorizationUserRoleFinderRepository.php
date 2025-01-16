<?php

namespace App\Module\Authorization\Repository;

use App\Core\Infrastructure\Database\QueryFactory;

final readonly class AuthorizationUserRoleFinderRepository
{
    public function __construct(
        private QueryFactory $queryFactory,
    ) {
    }

    /**
     * Get role hierarchy from a user that has status active.
     *
     * @param int $userId
     *
     * @return int user role hierarchy (lower hierarchy means higher privileged role)
     */
    public function getRoleHierarchyByUserId(int $userId): int
    {
        $query = $this->queryFactory->selectQuery()
            ->select(['user_role.hierarchy'])
            ->from('user')
            ->leftJoin('user_role', ['user.user_role_id = user_role.id'])
            ->where(['user.deleted_at IS' => null, 'user.id' => $userId]);
        $roleResultRow = $query->execute()->fetch('assoc');

        // If no role found, return highest hierarchy which means lowest privileged role
        return (int)($roleResultRow['hierarchy'] ?? 1000);
    }

    /**
     * Get user role hierarchies mapped by name or id if param is true.
     *
     * @param bool $mappedById if key of return array should be the role id or name
     *
     * @return array<string|int, int>
     */
    public function getUserRolesHierarchies(bool $mappedById = false): array
    {
        $query = $this->queryFactory->selectQuery()->select(['id', 'name', 'hierarchy'])->from('user_role');
        $resultRows = $query->execute()->fetchAll('assoc');

        $userRoles = [];
        foreach ($resultRows as $resultRow) {
            if ($mappedById === false) {
                $userRoles[$resultRow['name']] = (int)$resultRow['hierarchy'];
            } else {
                $userRoles[$resultRow['id']] = (int)$resultRow['hierarchy'];
            }
        }

        return $userRoles;
    }

    /**
     * Find the id of a user role with the given name.
     * Used only in testing.
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
