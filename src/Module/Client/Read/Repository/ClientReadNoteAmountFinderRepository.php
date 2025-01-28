<?php

namespace App\Module\Client\Read\Repository;

use App\Infrastructure\Database\QueryFactory;

readonly class ClientReadNoteAmountFinderRepository
{
    public function __construct(
        private QueryFactory $queryFactory,
    ) {
    }

    /**
     * Find the amount of notes without counting the main note.
     *
     * @param int $clientId
     *
     * @return int
     */
    public function findClientNotesAmount(int $clientId): int
    {
        $query = $this->queryFactory->selectQuery()->from('client');

        $query->select(['amount' => $query->func()->count('n.id')])
            ->join(['n' => ['table' => 'note', 'type' => 'LEFT', 'conditions' => 'n.client_id = client.id']])
            ->where(
                [
                    'client.id' => $clientId,
                    // The main note should not be counted in
                    'n.is_main' => 0,
                    'n.deleted_at IS' => null,
                ]
            );

        // Return amount of notes
        return (int)$query->execute()->fetch('assoc')['amount'];
    }
}
