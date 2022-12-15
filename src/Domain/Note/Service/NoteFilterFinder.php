<?php

namespace App\Domain\Note\Service;

use App\Domain\Authorization\UnauthorizedException;
use App\Domain\Note\Data\NoteResultData;
use App\Domain\Note\Exception\InvalidNoteFilterException;
use Odan\Session\SessionInterface;

class NoteFilterFinder
{
    public function __construct(
        private readonly NoteFinder $noteFinder,
        private readonly SessionInterface $session,
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
        if (isset($params['most-recent']) && (int)$params['most-recent'] === 1) {
            return $this->noteFinder->findMostRecentNotes();
        }
        // Filter client id
        if (isset($params['client_id'])) {
            // To display own notes, the client sends the filter user=session
            if (is_numeric($params['client_id'])) {
                // User is already logged in as UserAuthenticationMiddleware is present for the note group
                return $this->noteFinder->findAllNotesFromClientExceptMain((int)$params['client_id']);
            }
            // Exception message tested in NoteFilterProvider.php
            throw new InvalidNoteFilterException('client_id has to be numeric.');
        }
        // Filter 'user'
        if (isset($params['user'])) {
            // To display own notes, the client sends the filter user=session
            if ($params['user'] === 'session') {
                // User has to be logged-in to access own-notes
                if (($userId = $this->session->get('user_id')) !== null) {
                    $params['user'] = $userId;
                } else {
                    throw new UnauthorizedException('You have to be logged in to access own-notes');
                }
            } // If not user 'session' and also not numeric
            elseif (!is_numeric($params['user'])) {
                // Exception message tested in NoteFilterProvider.php
                throw new InvalidNoteFilterException('Filter "user" is not numeric.');
            }

            return $this->noteFinder->findAllNotesFromUser((int)$params['user']);
        }

        // Other filters here

        // If there is no filter, an empty array is returned
        return [];
    }
}
