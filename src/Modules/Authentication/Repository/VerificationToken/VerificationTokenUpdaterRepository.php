<?php

namespace App\Modules\Authentication\Repository\VerificationToken;

use App\Core\Infrastructure\Factory\QueryFactory;

final readonly class VerificationTokenUpdaterRepository
{
    public function __construct(
        private QueryFactory $queryFactory,
    ) {
    }

    /**
     * Set the verification token to "used".
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
