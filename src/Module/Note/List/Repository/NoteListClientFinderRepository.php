<?php

namespace App\Module\Note\List\Repository;

use App\Core\Infrastructure\Factory\QueryFactory;
use App\Module\Client\Data\ClientData;

readonly class NoteListClientFinderRepository
{
    public function __construct(
        private QueryFactory $queryFactory,
    ) {
    }

    /**
     * Return client including deleted with given id if it exists.
     *
     * @param string|int $id
     *
     * @return ClientData
     */
    public function findClientData(string|int $id): ClientData
    {
        $query = $this->queryFactory->selectQuery()->select(['*'])->from('client')->where(
            ['id' => $id]
        );

        $clientRow = $query->execute()->fetch('assoc') ?: [];

        return new ClientData($clientRow);
    }
}