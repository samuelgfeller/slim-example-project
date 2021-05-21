<?php


namespace App\Infrastructure\Authentication;


use App\Domain\User\DTO\User;
use App\Infrastructure\DataManager;

class UserRegistererRepository
{
    public function __construct(
        private DataManager $dataManager
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
        return (int)$this->dataManager->newInsert($userRows)->into('user')->execute()->lastInsertId();
    }
}