<?php


namespace App\Infrastructure\User;


use App\Infrastructure\DataManager;

class UserDeleterRepository
{
    public function __construct(
        private DataManager $dataManager
    ) { }

    /**
     * Delete user from database
     *
     * @param int $userId
     * @return bool
     */
    public function deleteUserById(int $userId): bool
    {
        $query = $this->dataManager->newDelete('user')->where(['id' => $userId]);
        return $query->execute()->rowCount() > 0;
    }

}