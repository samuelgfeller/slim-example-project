<?php


namespace App\Infrastructure\Authentication\VerificationToken;


use App\Infrastructure\Factory\QueryFactory;

class VerificationTokenUpdaterRepository
{
    public function __construct(
        private QueryFactory $queryFactory
    ) { }

    /**
     * Set verification token to used
     *
     * @param int $verificationId
     * @return bool
     */
    public function setVerificationEntryToUsed(int $verificationId): bool
    {
        $query = $this->queryFactory->newQuery();
        $query->update('user_verification')->set(['used_at' => $query->newExpr('NOW()')])->where(
            ['id' => $verificationId]
        );
        return $query->execute()->rowCount() > 0;
    }
}