<?php

namespace App\Module\Note\Find\Service;

use App\Module\Note\Data\NoteData;
use App\Module\Note\Find\Repository\NoteFinderRepository;

final readonly class NoteFinder
{
    public function __construct(
        private NoteFinderRepository $noteFinderRepository,
    ) {
    }

    /**
     * Find one note in the database.
     *
     * @param int $id
     *
     * @return NoteData
     */
    public function findNote(int $id): NoteData
    {
        return $this->noteFinderRepository->findNoteById($id);
    }
}
