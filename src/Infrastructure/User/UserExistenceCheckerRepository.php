<?php


namespace App\Infrastructure\User;


use App\Infrastructure\DataManager;

class UserExistenceCheckerRepository
{

    public function __construct(
        private DataManager $dataManager
    ) { }

    /**
     * Retrieve user role
     *
     * @param int $id
     * @return bool
     */
    public function userExists(int $id): bool
    {
        return $this->dataManager->exists('user', 'id', $id);
    }
}