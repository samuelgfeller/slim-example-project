<?php


namespace App\Infrastructure\Client;


use App\Infrastructure\Factory\QueryFactory;

class ClientUpdaterRepository

{
    public function __construct(
        private QueryFactory $queryFactory
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
        $query = $this->queryFactory->newQuery()->update('post')->set($data)->where(['id' => $id]);
        return $query->execute()->rowCount() > 0;
    }
}