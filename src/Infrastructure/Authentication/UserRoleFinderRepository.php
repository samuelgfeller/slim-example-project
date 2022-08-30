<?php


namespace App\Infrastructure\Authentication;


use App\Infrastructure\Exceptions\PersistenceRecordNotFoundException;
use App\Infrastructure\Factory\QueryFactory;

class UserRoleFinderRepository
{
    public function __construct(
        private QueryFactory $queryFactory
    ) { }

    /**
     * Retrieve user role
     *
     * @param int $userId
     * @return string
     * @throws PersistenceRecordNotFoundException if entry not found
     */
    public function getUserRoleById(int $userId): string
    {
        // todo put role in separate tables
        $query = $this->queryFactory->newQuery()->select(['role'])->from('user')->where(
            ['deleted_at IS' => null, 'id' => $userId]);
        $role = $query->execute()->fetch('assoc')['role'];
        if (!$role){
            throw new PersistenceRecordNotFoundException('user');
        }
        return $role;
    }
}