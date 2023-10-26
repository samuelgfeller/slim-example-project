<?php

namespace App\Infrastructure\Authentication;

use App\Domain\User\Data\UserRoleData;
use App\Domain\User\Enum\UserRole;
use App\Infrastructure\Factory\QueryFactory;

class UserRoleFinderRepository
{
    public function __construct(
        private readonly QueryFactory $queryFactory
    ) {
    }

    /**
     * Check if user role with given id exists.
     *
     * @param int|string $userRoleId
     * @return bool
     */
    public function userRoleWithIdExists(string|int $userRoleId): bool
    {
        $query = $this->queryFactory->selectQuery()->from('user_role')->select(['id'])->where(['id' => $userRoleId]);
        return $query->execute()->fetch('assoc') !== false;
    }

    /**
     * Get role data from user that has status active.
     *
     * @param int $userId
     *
     * @return UserRoleData empty object if user is not active
     */
    public function getUserRoleDataFromUser(int $userId): UserRoleData
    {
        $query = $this->queryFactory->selectQuery()
            ->select(['user_role.id', 'user_role.name', 'user_role.hierarchy'])
            ->from('user')
            ->leftJoin('user_role', ['user.user_role_id = user_role.id'])
            ->where(['user.deleted_at IS' => null, 'user.id' => $userId]);
        $roleResultRow = $query->execute()->fetch('assoc');
        if ($roleResultRow !== false) {
            $userRoleData = new UserRoleData();
            $userRoleData->id = $roleResultRow['id'];
            $userRoleData->name = $roleResultRow['name'];
            $userRoleData->hierarchy = $roleResultRow['hierarchy'];

            return $userRoleData;
        }

        return new UserRoleData();
    }

    /**
     * Get user role hierarchies mapped by name or id if param is true.
     *
     * @param bool $mappedById if key of return array should be the role id or name
     *
     * @return array{role_name: int}
     */
    public function getUserRolesHierarchies(bool $mappedById = false): array
    {
        $query = $this->queryFactory->selectQuery()->select(['id', 'name', 'hierarchy'])->from('user_role');
        $resultRows = $query->execute()->fetchAll('assoc');

        $userRoles = [];
        foreach ($resultRows as $resultRow) {
            if ($mappedById === false) {
                $userRoles[$resultRow['name']] = $resultRow['hierarchy'];
            } else {
                $userRoles[$resultRow['id']] = $resultRow['hierarchy'];
            }
        }

        return $userRoles;
    }

    /**
     * Return all user roles with as key the id and value the name.
     *
     * @return array{id: string, name: string}
     */
    public function findAllUserRolesForDropdown(): array
    {
        $query = $this->queryFactory->selectQuery()->from('user_role');

        $query->select(['id', 'name']);
        $resultRows = $query->execute()->fetchAll('assoc') ?: [];
        $userRoles = [];
        foreach ($resultRows as $resultRow) {
            $userRoles[(int)$resultRow['id']] = UserRole::from($resultRow['name'])->roleNameForDropdown();
        }

        return $userRoles;
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
