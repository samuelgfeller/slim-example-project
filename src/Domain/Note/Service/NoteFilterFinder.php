<?php


namespace App\Domain\Note\Service;


use App\Domain\Exceptions\UnauthorizedException;
use App\Domain\Note\Data\NoteWithUserData;
use App\Domain\Note\Exception\InvalidNoteFilterException;
use Odan\Session\SessionInterface;
use Slim\Exception\HttpUnauthorizedException;

class NoteFilterFinder
{
    public function __construct(
        private NoteFinder $noteFinder,
        private SessionInterface $session,
    )
    {
    }

    /**
     * Return notes matching given filter.
     * If there is no filter, all notes are returned.
     *
     * @param array $params GET parameters containing filter values
     *
     * @return NoteWithUserData[]
     */
    public function findNotesWithFilter(array $params): array
    {
        // Filter 'user'
        if (isset($params['user'])) {
            // To display own notes, the client sends the filter user=session
            if ($params['user'] === 'session'){
                // User has to be logged-in to access own-notes
                if(($userId = $this->session->get('user_id')) !== null){
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

        // If there is no filter, all notes should be returned
        return $this->noteFinder->findAllNotesWithUsers();
    }
}