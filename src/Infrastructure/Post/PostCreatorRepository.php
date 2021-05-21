<?php


namespace App\Infrastructure\Post;


use App\Infrastructure\DataManager;

class PostCreatorRepository

{
    public function __construct(
        private DataManager $dataManager
    )
    {
    }

    /**
     * Insert post in database
     *
     * @param array $data key is column name
     * @return int lastInsertId
     */
    public function insertPost(array $data): int
    {
        return (int)$this->dataManager->newInsert($data)->into('post')->execute()->lastInsertId();
    }
}