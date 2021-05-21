<?php


namespace App\Infrastructure\Authentication\VerificationToken;


use App\Domain\Authentication\DTO\UserVerification;
use App\Infrastructure\DataManager;

class VerificationTokenFinderRepository
{

    public function __construct(
        private DataManager $dataManager
    ) { }

    /**
     * Search and return user verification entry with token
     *
     * @param int $id
     * @return UserVerification
     */
    public function findUserVerification(int $id): UserVerification
    {
        $userVerificationRow = $this->dataManager->findById('user_verification', $id);
        return new UserVerification($userVerificationRow);
    }

    /**
     * @param int $verificationId
     *
     * @return int
     */
    public function getUserIdFromVerification(int $verificationId): int
    {
        // Cake query builder return value is string
        return (int)$this->dataManager->findById('user_verification', $verificationId, ['user_id'])['user_id'];
    }
}