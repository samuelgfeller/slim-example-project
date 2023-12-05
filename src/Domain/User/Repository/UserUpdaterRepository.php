<?php

namespace App\Domain\User\Repository;

use App\Domain\Factory\Infrastructure\QueryFactory;
use App\Domain\User\Enum\UserStatus;

readonly class UserUpdaterRepository
{
    public function __construct(
        private QueryFactory $queryFactory
    ) {
    }

    /**
     * Update values from user
     * Example of $data: ['firstName' => 'NewFirstName'].
     *
     * @param int $userId
     * @param array $userValues has to be only allowed changes for this function
     *
     * @return bool
     */
    public function updateUser(int $userId, array $userValues): bool
    {
        $query = $this->queryFactory->updateQuery()->update('user')->set($userValues)->where(['id' => $userId]);

        return $query->execute()->rowCount() > 0;
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

    /**
     * Change user password.
     *
     * @param string $passwordHash
     * @param int $userId
     *
     * @return bool
     */
    public function changeUserPassword(string $passwordHash, int $userId): bool
    {
        $query = $this->queryFactory->updateQuery()->update('user')->set(['password_hash' => $passwordHash])->where(
            ['id' => $userId]
        );

        return $query->execute()->rowCount() > 0;
    }
}
