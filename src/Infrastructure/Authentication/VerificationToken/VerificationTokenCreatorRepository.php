<?php


namespace App\Infrastructure\Authentication\VerificationToken;


use App\Infrastructure\DataManager;

class VerificationTokenCreatorRepository
{
    public function __construct(
        private DataManager $dataManager
    ) { }

    /**
     * Insert new user verification token
     *
     * @param array $data
     * @return int
     */
    public function insertUserVerification(array $data): int
    {
        return (int)$this->dataManager->newInsert($data)->into('user_verification')->execute()->lastInsertId();
    }
}