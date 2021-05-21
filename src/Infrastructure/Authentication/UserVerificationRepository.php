<?php

declare(strict_types=1);

namespace App\Infrastructure\Authentication;

use App\Common\Hydrator;
use App\Domain\Authentication\DTO\UserVerification;
use App\Infrastructure\DataManager;

/**
 * Needed to define the table
 */
class UserVerificationRepository
{

    /**
     * UserVerificationRepository constructor.
     *
     * @param DataManager $dataManager
     * @param Hydrator $hydrator
     */
    public function __construct(private DataManager $dataManager, private Hydrator $hydrator)
    {
    }

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

    /**
     * Delete verification entry with user id
     *
     * @param int $verificationId
     * @return bool
     */
    public function setVerificationEntryToUsed(int $verificationId): bool
    {
        $query = $this->dataManager->newQuery();
        $query->update('user_verification')->set(['used_at' => $query->newExpr('NOW()')])->where(
            ['id' => $verificationId]
        );
        return $query->execute()->rowCount() > 0;
    }


}
