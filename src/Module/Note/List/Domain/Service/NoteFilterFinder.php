<?php

namespace App\Module\Note\List\Domain\Service;

use App\Module\Note\List\Data\NoteResultData;
use App\Module\Note\List\Domain\Exception\InvalidNoteFilterException;

final readonly class NoteFilterFinder
{
    public function __construct(
        private NoteListFinder $noteListFinder,
    ) {
    }

    /**
     * Return notes matching given filter.
     * If there is no filter, all notes are returned.
     *
     * @param array $params GET parameters containing filter values
     *
     * @return NoteResultData[]
     */
    public function findNotesWithFilter(array $params): array
    {
        // Filter client id
        if (isset($params['most-recent'])) {
            if (is_numeric($params['most-recent'])) {
                return $this->noteListFinder->findMostRecentNotes((int)$params['most-recent']);
            }
            throw new InvalidNoteFilterException('Value has to be numeric.');
        }
        // Filter client id
        if (isset($params['client_id'])) {
            // To display own notes, the client sends the filter user=session
            if (is_numeric($params['client_id'])) {
                // User is already logged in as UserAuthenticationMiddleware is present for the note group
                return $this->noteListFinder->findAllNotesFromClientExceptMain((int)$params['client_id']);
            }
            // Exception message tested in NoteFilterProvider.php
            throw new InvalidNoteFilterException('Value has to be numeric.');
        }
        // Filter 'user'
        if (isset($params['user'])) {
            if (!is_numeric($params['user'])) {
                // Exception message tested in NoteFilterProvider.php
                throw new InvalidNoteFilterException('Value has to be numeric.');
            }

            // Get notes from user and return them
            return $this->noteListFinder->findAllNotesExceptMainFromUser((int)$params['user']);
        }

        // Other filters here

        // If there is no filter, an empty array is returned
        return [];
    }
}
