<?php

namespace App\Module\Note\Update\Repository;

use App\Core\Infrastructure\Database\QueryFactory;

final readonly class NoteUpdaterRepository
{
    public function __construct(
        private QueryFactory $queryFactory,
    ) {
    }

    /**
     * Update values from note.
     *
     * @param int $id
     * @param array $data ['col_name' => 'New name']
     *
     * @return bool
     */
    public function updateNote(array $data, int $id): bool
    {
        $query = $this->queryFactory->updateQuery()->update('note')->set($data)->where(['id' => $id]);

        return $query->execute()->rowCount() > 0;
    }
}
