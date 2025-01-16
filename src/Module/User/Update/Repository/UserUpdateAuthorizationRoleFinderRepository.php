<?php

namespace App\Module\User\Update\Repository;

use App\Core\Infrastructure\Database\QueryFactory;
use App\Module\User\Data\UserRoleData;

final readonly class UserUpdateAuthorizationRoleFinderRepository
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
}
