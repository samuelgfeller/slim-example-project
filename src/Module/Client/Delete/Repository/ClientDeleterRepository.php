<?php

namespace App\Module\Client\Delete\Repository;

use App\Infrastructure\Database\QueryFactory;

final readonly class ClientDeleterRepository
{
    public function __construct(
        private QueryFactory $queryFactory,
    ) {
    }

    /**
     * Mark client as deleted in database.
     *
     * @param int $id
     *
     * @return bool
     */
    public function deleteClient(int $id): bool
    {
        $query = $this->queryFactory->softDeleteQuery('client')->where(['id' => $id]);

        return $query->execute()->rowCount() > 0;
    }

    /**
     * Delete client from database permanently.
     *
     * @param int $id
     *
     * @return bool
     */
    public function hardDeleteClient(int $id): bool
    {
        $query = $this->queryFactory->hardDeleteQuery()->delete('client')->where(['id' => $id]);

        return $query->execute()->rowCount() > 0;
    }
}
