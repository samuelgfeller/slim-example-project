<?php


namespace App\Infrastructure\Authentication\VerificationToken;


use App\Domain\Authentication\DTO\UserVerification;
use App\Infrastructure\Exceptions\PersistenceRecordNotFoundException;
use App\Infrastructure\Factory\QueryFactory;

class VerificationTokenFinderRepository
{

    public function __construct(
        private QueryFactory $queryFactory
    ) { }

    /**
     * Search and return user verification entry with token
     *
     * @param int $id
     * @return UserVerification
     */
    public function findUserVerification(int $id): UserVerification
    {
        $query = $this->queryFactory->newQuery()->select(['*'])->from('user_verification')->where(
            ['deleted_at IS' => null, 'id' => $id]);
        $userVerificationRow = $query->execute()->fetch('assoc') ?: [];
        return new UserVerification($userVerificationRow);
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
            ['deleted_at IS' => null, 'id' => $verificationId]);
        // Cake query builder return value is string
        $userId = $query->execute()->fetch('assoc')['user_id'];
        if (!$userId){
            throw new PersistenceRecordNotFoundException('post');
        }
        return (int)$userId;


    }
}