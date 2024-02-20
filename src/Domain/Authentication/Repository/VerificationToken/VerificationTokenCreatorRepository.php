<?php

namespace App\Domain\Authentication\Repository\VerificationToken;

use App\Infrastructure\Factory\QueryFactory;

final readonly class VerificationTokenCreatorRepository
{
    public function __construct(
        private QueryFactory $queryFactory
    ) {
    }

    /**
     * Insert new user verification token.
     *
     * @param array $data
     *
     * @return int
     */
    public function insertUserVerification(array $data): int
    {
        return (int)$this->queryFactory->insertQueryWithData($data)->into('user_verification')->execute()->lastInsertId();
    }
}
