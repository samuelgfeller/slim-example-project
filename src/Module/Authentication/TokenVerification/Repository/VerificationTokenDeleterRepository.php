<?php

namespace App\Module\Authentication\TokenVerification\Repository;

use App\Infrastructure\Database\QueryFactory;

final readonly class VerificationTokenDeleterRepository
{
    public function __construct(
        private QueryFactory $queryFactory,
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
