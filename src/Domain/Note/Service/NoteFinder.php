<?php


namespace App\Domain\Note\Service;


use App\Domain\Authorization\Privilege;
use App\Domain\Note\Authorization\NoteAuthorizationGetter;
use App\Domain\Note\Data\NoteData;
use App\Domain\Note\Data\NoteWithUserData;
use App\Infrastructure\Client\ClientFinderRepository;
use App\Infrastructure\Note\NoteFinderRepository;

class NoteFinder
{
    public function __construct(
        private readonly NoteFinderRepository $noteFinderRepository,
        private readonly NoteAuthorizationGetter $noteAuthorizationGetter,
        private readonly ClientFinderRepository $clientFinderRepository,
    ) {
    }

    /**
     * Populate $privilege attribute of given NoteWithUserData array
     *
     * @param array{NoteWithUserData} $notes
     * @param int|null $clientOwnerId if client owner id not provided, client id should be passed in next parameter
     * @param int|null $clientId
     *
     * @return void In PHP, an object variable doesn't contain the object itself as value. It only contains an object
     * identifier meaning the reference is passed and changes are made on the original reference that can be used further
     * https://www.php.net/manual/en/language.oop5.references.php; https://stackoverflow.com/a/65805372/9013718
     */
    private function setNotePrivilegeAndRemoveMessageOfHidden(
        array $notes,
        ?int $clientOwnerId = null,
        ?int $clientId = null
    ): void {
        $randomText = 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor 
invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo 
duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit 
amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt 
ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores 
et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.';

        // Get client owner id if not given
        if ($clientOwnerId === null && $clientId !== null) {
            $clientOwnerId = $this->clientFinderRepository->findClientById($clientId)->userId;
        }

        foreach ($notes as $userNote) {
            // Privilege only create possible if user may not see the note but may create one
            $userNote->privilege = $this->noteAuthorizationGetter->getNotePrivilege(
                $userNote->userId,
                $clientOwnerId,
                $userNote->noteHidden,
            );
            // If not allowed to read
            if (!$userNote->privilege->hasPrivilege(Privilege::READ)) {
                // Change message of note to lorem ipsum
                $userNote->noteMessage = substr($randomText, 0, strlen($userNote->noteMessage));
                // Remove line breaks and extra spaces from string
                $userNote->noteMessage = preg_replace('/\s\s+/', ' ', $userNote->noteMessage);
            }
        }
    }

    /**
     * Find one note in the database
     *
     * @param $id
     * @return NoteData
     */
    public function findNote($id): NoteData
    {
        return $this->noteFinderRepository->findNoteById($id);
    }

    /**
     * Return all notes which are linked to the given user
     *
     * @param int $userId
     * @return NoteWithUserData[]
     */
    public function findAllNotesFromUser(int $userId): array
    {
        $allNotes = $this->noteFinderRepository->findAllNotesByUserId($userId);
        $this->changeDateFormat($allNotes);
        $this->setNotePrivilegeAndRemoveMessageOfHidden($allNotes);
        return $allNotes;
    }

    /**
     * Return all notes which are linked to the given user
     *
     * @param int $notesAmount
     * @return NoteWithUserData[]
     */
    public function findMostRecentNotes(int $notesAmount = 10): array
    {
        $allNotes = $this->noteFinderRepository->findMostRecentNotes($notesAmount);
        $this->changeDateFormat($allNotes, 'd. F Y • H:i'); // F is the full month name in english
        $this->setNotePrivilegeAndRemoveMessageOfHidden($allNotes);
        return $allNotes;
    }

    /**
     * Return all notes except the main note that are linked to the given client
     *
     * @param int $clientId
     * @return NoteWithUserData[]
     */
    public function findAllNotesFromClientExceptMain(int $clientId): array
    {
        $allNotes = $this->noteFinderRepository->findAllNotesExceptMainWithUserByClientId($clientId);
        // In PHP, an object variable doesn't contain the object itself as value. It only contains an object identifier
        // meaning the reference is passed and changes are made on the original reference that can be used further
        // https://www.php.net/manual/en/language.oop5.references.php; https://stackoverflow.com/a/65805372/9013718
        $this->changeDateFormat($allNotes, 'd. F Y • H:i'); // F is the full month name in english
        $this->setNotePrivilegeAndRemoveMessageOfHidden($allNotes, null, $clientId);
        return $allNotes;
    }

    /**
     * Return the number of notes attached to a client
     *
     * @param int $clientId
     * @return int
     */
    public function findClientNotesAmount(int $clientId): int
    {
        return $this->noteFinderRepository->findClientNotesAmount($clientId);
    }


    /**
     * Change created and updated date format from SQL datetime to
     * something we are used to see in Switzerland
     *
     * @param NoteWithUserData[] $userNotes
     * @param string $format If default format changes, it has to be adapted in NoteListActionTest
     *
     * @return void
     */
    private function changeDateFormat(array $userNotes, string $format = 'd.m.Y H:i:s'): void
    {
        // Tested in NoteListActionTest
        foreach ($userNotes as $userNote) {
            // Change updated at format
            $userNote->noteUpdatedAt = $userNote->noteUpdatedAt ? (new \DateTime($userNote->noteUpdatedAt))
                ->format($format) : null;
            // Change created at format
            $userNote->noteCreatedAt = $userNote->noteCreatedAt ? (new \DateTime($userNote->noteCreatedAt))
                ->format($format) : null;
        }
    }
}