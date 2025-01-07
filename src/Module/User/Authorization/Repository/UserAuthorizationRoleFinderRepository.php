<?php

namespace App\Module\User\Authorization\Repository;

use App\Core\Infrastructure\Factory\QueryFactory;
use App\Module\User\Data\UserRoleData;
use App\Module\User\Enum\UserRole;

final readonly class UserAuthorizationRoleFinderRepository
{
    public function __construct(
        private QueryFactory $queryFactory,
    ) {
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
