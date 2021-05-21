<?php


namespace App\Infrastructure\Authentication\VerificationToken;


use App\Infrastructure\DataManager;

class VerificationTokenDeleterRepository
{

    public function __construct(
        private DataManager $dataManager
    ) { }

    /**
     * Delete verification entry with user id
     *
     * @param int $userId
     * @return bool
     */
    public function deleteVerificationToken(int $userId): bool
    {
        $query = $this->dataManager->newDelete('user_verification')->where(['user_id' => $userId]);
        return $query->execute()->rowCount() > 0;
    }
}