<?php

namespace App\Module\Client\Validation\Repository;

use App\Core\Infrastructure\Database\QueryFactory;

final readonly class ClientStatusValidatorRepository
{
    public function __construct(
        private QueryFactory $queryFactory,
    ) {
    }

    /**
     * Check if given client status id exists.
     *
     * @param int $clientStatusId
     *
     * @return bool
     */
    public function clientStatusExists(int $clientStatusId): bool
    {
        $query = $this->queryFactory->selectQuery()->from('client_status');

        $query->select(['id'])->where(['id' => $clientStatusId, 'deleted_at IS' => null]);
        $resultRow = $query->execute()->fetch('assoc') ?: [];

        return !empty($resultRow);
    }
}
