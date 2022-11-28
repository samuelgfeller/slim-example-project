<?php


namespace App\Infrastructure\User;


use App\Domain\User\Data\UserData;
use App\Infrastructure\Factory\QueryFactory;

class UserCreatorRepository
{
    public function __construct(
        private QueryFactory $queryFactory
    ) { }

    /**
     * Insert user in database
     *
     * @param UserData $user
     * @return int lastInsertId
     */
    public function insertUser(UserData $user): int
    {
        $userRows = $user->toArrayForDatabase();
        return (int)$this->queryFactory->newInsert($userRows)->into('user')->execute()->lastInsertId();
    }
}