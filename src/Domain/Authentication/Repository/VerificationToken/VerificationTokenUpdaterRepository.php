<?php

namespace App\Domain\Authentication\Repository\VerificationToken;

use App\Domain\Factory\Infrastructure\QueryFactory;

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
        $query = $this->queryFactory->updateQuery();
        $query->update('user_verification')->set($updateRow)->where(
            ['id' => $verificationId]
        );

        return $query->execute()->rowCount() > 0;
    }
}