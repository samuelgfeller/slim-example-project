<?php

namespace App\Module\Authentication\PasswordReset\Repository;

use App\Infrastructure\Database\QueryFactory;

class PasswordChangerRepository
{
    public function __construct(
        private QueryFactory $queryFactory,
    ) {
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
