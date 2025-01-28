<?php

namespace App\Module\Authentication\TokenVerification\Repository;

use App\Infrastructure\Database\QueryFactory;

final readonly class VerificationTokenCreatorRepository
{
    public function __construct(
        private QueryFactory $queryFactory,
    ) {
    }

    /**
     * Insert a new user verification token.
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
