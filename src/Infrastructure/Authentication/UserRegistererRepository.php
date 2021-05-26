<?php


namespace App\Infrastructure\Authentication;


use App\Domain\User\DTO\User;
use App\Infrastructure\Factory\QueryFactory;

class UserRegistererRepository
{
    public function __construct(
        private QueryFactory $queryFactory
    ) { }

    /**
     * Insert user in database
     *
     * @param User $user
     * @return int lastInsertId
     */
    public function insertUser(User $user): int
    {
        $userRows = $user->toArrayForDatabase();
        return (int)$this->queryFactory->newInsert($userRows)->into('user')->execute()->lastInsertId();
    }
}