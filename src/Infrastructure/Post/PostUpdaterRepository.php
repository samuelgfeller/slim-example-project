<?php


namespace App\Infrastructure\Post;


use App\Infrastructure\DataManager;

class PostUpdaterRepository

{
    public function __construct(
        private DataManager $dataManager
    )
    {
    }

    /**
     * Update values from post
     *
     * @param int $id
     * @param array $data ['col_name' => 'New name']
     * @return bool
     */
    public function updatePost(array $data, int $id): bool
    {
        $query = $this->dataManager->newQuery()->update('post')->set($data)->where(['id' => $id]);
        return $query->execute()->rowCount() > 0;
    }
}