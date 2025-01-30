<?php

namespace App\Module\Authentication\ChangeUserStatus\Repository;

use App\Infrastructure\Database\QueryFactory;
use App\Module\User\Enum\UserStatus;

final readonly class UserStatusUpdaterRepository
{
    public function __construct(
        private QueryFactory $queryFactory,
    ) {
    }

    /**
     * Change user status.
     *
     * @param UserStatus $status
     * @param string|int|null $userId
     *
     * @return bool
     */
    public function changeUserStatus(UserStatus $status, string|int|null $userId): bool
    {
        $query = $this->queryFactory->updateQuery()->update('user')->set(['status' => $status->value])->where(
            ['id' => $userId]
        );

        return $query->execute()->rowCount() > 0;
    }
}
