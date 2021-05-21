<?php


namespace App\Infrastructure\Authentication;


use App\Infrastructure\DataManager;

class UserRoleFinderRepository
{
    public function __construct(
        private DataManager $dataManager
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
        return $this->dataManager->getById('user', $id, ['role'])['role'];
    }
}