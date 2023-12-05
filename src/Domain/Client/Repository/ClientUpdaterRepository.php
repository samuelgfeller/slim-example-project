<?php

namespace App\Domain\Client\Repository;

use App\Domain\Factory\Infrastructure\QueryFactory;

readonly class ClientUpdaterRepository
{
    public function __construct(
        private QueryFactory $queryFactory
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
        $query = $this->queryFactory->updateQuery()->update('client')->set($data)->where(['id' => $clientId]);

        return $query->execute()->rowCount() > 0;
    }
}
