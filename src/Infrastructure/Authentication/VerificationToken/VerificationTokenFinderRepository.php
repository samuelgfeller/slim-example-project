<?php

namespace App\Infrastructure\Authentication\VerificationToken;

use App\Domain\Authentication\Data\UserVerificationData;
use App\Domain\User\Data\UserData;
use App\Infrastructure\Exceptions\PersistenceRecordNotFoundException;
use App\Infrastructure\Factory\QueryFactory;

class VerificationTokenFinderRepository
{
    public function __construct(
        private readonly QueryFactory $queryFactory
    ) {
    }

    /**
     * Search and return user verification entry with token.
     *
     * @param int $id
     *
     * @return UserVerificationData
     */
    public function findUserVerification(int $id): UserVerificationData
    {
        $query = $this->queryFactory->newQuery()->select(['*'])->from('user_verification')->where(
            ['deleted_at IS' => null, 'id' => $id]
        );
        $userVerificationRow = $query->execute()->fetch('assoc') ?: [];

        return new UserVerificationData($userVerificationRow);
    }

    /**
     * @param int $verificationId
     *
     * @return int
     * Throws PersistenceRecordNotFoundException if entry not found
     */
    public function getUserIdFromVerification(int $verificationId): int
    {
        $query = $this->queryFactory->newQuery()->select(['user_id'])->from('user_verification')->where(
            ['deleted_at IS' => null, 'id' => $verificationId]
        );
        // Cake query builder return value is string
        $userId = $query->execute()->fetch('assoc')['user_id'];
        if (!$userId) {
            throw new PersistenceRecordNotFoundException('post');
        }

        return (int)$userId;
    }

    /**
     * Search and return user details of given verification entry
     * even if user_verification was deleted.
     *
     * @param int $verificationId
     *
     * @throws \Exception
     *
     * @return UserData
     */
    public function findUserDetailsByVerificationIncludingDeleted(int $verificationId): UserData
    {
        $query = $this->queryFactory->newQuery()->from('user_verification');

        $query->select(
            [
                'id' => 'user_verification.user_id',
                'email' => 'user.email',
            ]
        )->join(
            ['table' => 'user', 'conditions' => 'user_verification.user_id = user.id']
        )->andWhere(
            ['user_verification.id' => $verificationId]
        );
        $resultRows = $query->execute()->fetch('assoc') ?: [];

        return new UserData($resultRows);
    }
}
