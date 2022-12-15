<?php

namespace App\Infrastructure\Note;

use App\Infrastructure\Factory\QueryFactory;

class NoteValidatorRepository
{
    public function __construct(
        private readonly QueryFactory $queryFactory,
    ) {
    }

    /**
     * Returns a bool value if the main note already exists.
     *
     * @param int $clientId
     *
     * @return bool
     */
    public function mainNoteAlreadyExistsForClient(int $clientId): bool
    {
        $query = $this->queryFactory->newQuery()->select(['id'])->from('note')->where(
            ['deleted_at IS' => null, 'client_id' => $clientId, 'is_main' => 1]
        );
        $note = $query->execute()->fetch('assoc') ?: false;

        return (bool)$note;
    }
}
