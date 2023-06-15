<?php

namespace App\Domain\Note\Service;

use App\Domain\Note\Data\NoteResultData;
use App\Domain\Note\Exception\InvalidNoteFilterException;

class NoteFilterFinder
{
    public function __construct(
        private readonly NoteFinder $noteFinder,
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
                return $this->noteFinder->findMostRecentNotes((int)$params['most-recent']);
            }
            throw new InvalidNoteFilterException('Value has to be numeric.');
        }
        // Filter client id
        if (isset($params['client_id'])) {
            // To display own notes, the client sends the filter user=session
            if (is_numeric($params['client_id'])) {
                // User is already logged in as UserAuthenticationMiddleware is present for the note group
                return $this->noteFinder->findAllNotesFromClientExceptMain((int)$params['client_id']);
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
            return $this->noteFinder->findAllNotesExceptMainFromUser((int)$params['user']);
        }

        // Other filters here

        // If there is no filter, an empty array is returned
        return [];
    }
}
