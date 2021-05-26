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
     * @param int $id
     * @return string
     * Throws PersistenceRecordNotFoundException if entry not found
     */
    public function getUserRoleById(int $id): string
    {
        // todo put role in separate tables
        $query = $this->queryFactory->newQuery()->select(['role'])->from('user')->where(
            ['deleted_at IS' => null, 'id' => $id]);
        $role = $query->execute()->fetch('assoc')['role'];
        if (!$role){
            throw new PersistenceRecordNotFoundException('post');
        }
        return $role;
    }
}