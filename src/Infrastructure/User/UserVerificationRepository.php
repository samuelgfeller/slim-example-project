<?php
declare(strict_types=1);

namespace App\Infrastructure\User;

use App\Common\Hydrator;
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
     * @return array
     */
    public function findUserVerification(int $id): array
    {
        return $this->dataManager->findById('user_verification', $id);
    }

    /**
     * @param $verificationId
     * @return string
     */
    public function getUserIdFromVerification($verificationId): string
    {
        return $this->dataManager->findById('user_verification', $verificationId, ['user_id'])['user_id'];
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
}
