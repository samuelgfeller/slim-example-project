<?php

namespace App\Domain\Note\Service;

use App\Domain\Authentication\Service\UserRoleFinder;
use App\Domain\Note\Data\NoteWithUserData;
use App\Domain\User\Data\MutationRight;
use Odan\Session\SessionInterface;

/**
 * The client should know when to display edit and delete icons
 * Admins can edit all notes, users only their own
 */
class NoteUserRightSetter
{
    public function __construct(
        private SessionInterface $session,
        private UserRoleFinder $userRoleFinder,
    ) { }

    /**
     * Populate $userMutationRights attribute to given UserNoteData or array with
     * logged-in user mutation right.
     *
     * I'm not sure if that is a good practice to accept collections and single objects both in the same function,
     * but I have already seen this in a PHP function and thought it was practical.
     * @param NoteWithUserData[]|NoteWithUserData $userNoteData
     *
     * @return void In PHP, an object variable doesn't contain the object itself as value. It only contains an object
     * identifier meaning the reference is passed and changes are made on the original reference that can be used further
     * https://www.php.net/manual/en/language.oop5.references.php; https://stackoverflow.com/a/65805372/9013718
     */
    public function setUserRightsOnNotes(array|NoteWithUserData $userNoteData): void
    {
        if (is_array($userNoteData)) {
            foreach ($userNoteData as $userNote) {
                $this->setUserRightsOnNote($userNote);
            }
        } else {
            $this->setUserRightsOnNote($userNoteData);
        }
    }

    /**
     * Add userUpdateRight attribute to given UserNoteData with
     * logged-in user mutation right.
     *
     * @param NoteWithUserData $userNote
 */
    private function setUserRightsOnNote(NoteWithUserData $userNote): void
    {
        // Default is no rights
        $userNote->userMutationRight = MutationRight::NONE;

        if (($loggedInUserId = $this->session->get('user_id')) !== null) {
            $userRole = $this->userRoleFinder->getUserRoleById($loggedInUserId);

            if ($userNote->userId === $loggedInUserId || $userRole === 'admin') {
                $userNote->userMutationRight = MutationRight::ALL;
            }
        }
    }
}