<?php


namespace App\Infrastructure\User;


use App\Infrastructure\Factory\QueryFactory;

class UserExistenceCheckerRepository
{

    public function __construct(
        private QueryFactory $queryFactory
    ) { }

    /**
     * Retrieve user role
     *
     * @param int $id
     * @return bool
     */
    public function userExists(int $id): bool
    {
        $query = $this->queryFactory->newQuery()->from('user');
        $query->select(1)->where(['id' => $id]);
        $row = $query->execute()->fetch();
        return !empty($row);
    }
}