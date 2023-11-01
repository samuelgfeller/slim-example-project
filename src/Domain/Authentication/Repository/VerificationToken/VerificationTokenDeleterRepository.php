<?php

namespace App\Domain\Authentication\Repository\VerificationToken;

use App\Domain\Factory\Infrastructure\QueryFactory;

class VerificationTokenDeleterRepository
{
    public function __construct(
        private readonly QueryFactory $queryFactory
    ) {
    }

    /**
     * Delete verification entry with user id.
     *
     * @param int $userId
     *
     * @return bool
     */
    public function deleteVerificationToken(int $userId): bool
    {
        $query = $this->queryFactory->softDeleteQuery('user_verification')->where(['user_id' => $userId]);

        return $query->execute()->rowCount() > 0;
    }
}