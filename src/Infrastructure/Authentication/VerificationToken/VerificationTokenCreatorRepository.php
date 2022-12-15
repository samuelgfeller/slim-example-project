<?php

namespace App\Infrastructure\Authentication\VerificationToken;

use App\Infrastructure\Factory\QueryFactory;

class VerificationTokenCreatorRepository
{
    public function __construct(
        private readonly QueryFactory $queryFactory
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
        return (int)$this->queryFactory->newInsert($data)->into('user_verification')->execute()->lastInsertId();
    }
}
