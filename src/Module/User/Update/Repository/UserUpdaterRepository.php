<?php

namespace App\Module\User\Update\Repository;

use App\Infrastructure\Database\QueryFactory;

final readonly class UserUpdaterRepository
{
    public function __construct(
        private QueryFactory $queryFactory,
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
}
