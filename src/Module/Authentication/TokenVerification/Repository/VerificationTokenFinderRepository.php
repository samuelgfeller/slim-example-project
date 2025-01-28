<?php

namespace App\Module\Authentication\TokenVerification\Repository;

use App\Infrastructure\Database\Exception\PersistenceRecordNotFoundException;
use App\Infrastructure\Database\QueryFactory;
use App\Module\Authentication\Data\UserVerificationData;
use App\Module\User\Data\UserData;

// Class cannot be readonly as it's mocked (doubled) in tests
// https://stackoverflow.com/questions/75128424/troubles-with-mocking-new-php-8-2-readonly-classes-with-phpunit-test-doubles
class VerificationTokenFinderRepository
{
    public function __construct(
        private readonly QueryFactory $queryFactory,
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
        $query = $this->queryFactory->selectQuery()->select(['*'])->from('user_verification')->where(
            ['deleted_at IS' => null, 'id' => $id]
        );
        $userVerificationRow = $query->execute()->fetch('assoc') ?: [];

        return new UserVerificationData($userVerificationRow);
    }

    /**
     * @param int $verificationId
     *
     * @throws PersistenceRecordNotFoundException
     *
     * @return int
     */
    public function getUserIdFromVerification(int $verificationId): int
    {
        $query = $this->queryFactory->selectQuery()->select(['user_id'])->from('user_verification')->where(
            ['deleted_at IS' => null, 'id' => $verificationId]
        );
        // Cake query builder return value is string
        $userId = $query->execute()->fetch('assoc')['user_id'];
        if (!$userId) {
            throw new PersistenceRecordNotFoundException('user_verification');
        }

        return (int)$userId;
    }

    /**
     * Search and return user details of given verification entry
     * even if user_verification was deleted.
     *
     * @param int $verificationId
     *
     * @return UserData
     */
    public function findUserDetailsByVerificationIncludingDeleted(int $verificationId): UserData
    {
        $query = $this->queryFactory->selectQuery()->from('user_verification');

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
