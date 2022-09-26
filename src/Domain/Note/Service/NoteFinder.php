<?php


namespace App\Domain\Note\Service;


use App\Domain\Note\Data\NoteData;
use App\Domain\Note\Data\NoteWithUserData;
use App\Domain\User\Service\UserFinder;
use App\Infrastructure\Note\NoteFinderRepository;

class NoteFinder
{
    public function __construct(
        private NoteFinderRepository $noteFinderRepository,
        private NoteUserRightSetter $noteUserRightSetter,
    ) {
    }

    /**
     * Gives all undeleted notes from db with name of user
     *
     * @return NoteData[]
     */
    public function findAllNotesWithUsers(): array
    {
        $allNotes = $this->noteFinderRepository->findAllNotesWithUsers();
        $this->changeDateFormat($allNotes);
        // In PHP, an object variable doesn't contain the object itself as value. It only contains an object identifier
        // meaning the reference is passed and changes are made on the original reference that can be used further
        // https://www.php.net/manual/en/language.oop5.references.php; https://stackoverflow.com/a/65805372/9013718
        $this->noteUserRightSetter->setUserRightsOnNotes($allNotes);
        return $allNotes;
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
        $this->noteUserRightSetter->setUserRightsOnNotes($allNotes);
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
        $this->changeDateFormat($allNotes, 'd. F Y • H:i'); // F is the full month name in english
        $this->noteUserRightSetter->setUserRightsOnNotes($allNotes);
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