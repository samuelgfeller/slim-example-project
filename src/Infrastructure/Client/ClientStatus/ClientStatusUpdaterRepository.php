<?php

namespace App\Infrastructure\Client\ClientStatus;

use App\Infrastructure\Factory\QueryFactory;

class ClientStatusUpdaterRepository
{
    public function __construct(
        private readonly QueryFactory $queryFactory,
    ) {
    }

    /**
     * Change client status.
     *
     * @param int $clientId
     * @param array $clientStatusUpdateData
     *
     * @return bool
     */
    public function changeClientStatus(int $clientId, array $clientStatusUpdateData): bool
    {
        $query = $this->queryFactory->newQuery()->update('client_status')->set($clientStatusUpdateData)
            ->where(['id' => $clientId]);

        return $query->execute()->rowCount() > 0;
    }
}
