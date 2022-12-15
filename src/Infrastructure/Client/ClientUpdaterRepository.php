<?php

namespace App\Infrastructure\Client;

use App\Infrastructure\Factory\QueryFactory;

class ClientUpdaterRepository
{
    public function __construct(
        private readonly QueryFactory $queryFactory
    ) {
    }

    /**
     * Update values from client.
     *
     * @param int $clientId
     * @param array $data ['col_name' => 'New name']
     *
     * @return bool
     */
    public function updateClient(array $data, int $clientId): bool
    {
        $query = $this->queryFactory->newQuery()->update('client')->set($data)->where(['id' => $clientId]);

        return $query->execute()->rowCount() > 0;
    }
}
