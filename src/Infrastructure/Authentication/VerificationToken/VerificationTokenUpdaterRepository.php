<?php

namespace App\Infrastructure\Authentication\VerificationToken;

use App\Infrastructure\Factory\QueryFactory;

class VerificationTokenUpdaterRepository
{
    public function __construct(
        private readonly QueryFactory $queryFactory,
    ) {
    }

    /**
     * Set verification token to used.
     *
     * @param int $verificationId
     * @param array $updateRow
     *
     * @return bool
     */
    public function updateUserVerificationRow(int $verificationId, array $updateRow): bool
    {
        $query = $this->queryFactory->newQuery();
        $query->update('user_verification')->set($updateRow)->where(
            ['id' => $verificationId]
        );

        return $query->execute()->rowCount() > 0;
    }
}
