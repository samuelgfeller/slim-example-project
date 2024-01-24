<?php

namespace App\Domain\Note\Service;

use App\Domain\Authorization\Privilege;
use App\Domain\Client\Service\Authorization\ClientPermissionVerifier;
use App\Domain\Client\Service\ClientFinder;
use App\Domain\Note\Data\NoteData;
use App\Domain\Note\Data\NoteResultData;
use App\Domain\Note\Repository\NoteFinderRepository;
use App\Domain\Note\Service\Authorization\NotePrivilegeDeterminer;

readonly class NoteFinder
{
    public function __construct(
        private NoteFinderRepository $noteFinderRepository,
        private NotePrivilegeDeterminer $notePrivilegeDeterminer,
        private ClientFinder $clientFinder,
        private ClientPermissionVerifier $clientPermissionVerifier,
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

    /**
     * Return all notes which are linked to the given user.
     *
     * @param int $userId
     *
     * @return NoteResultData[]
     */
    public function findAllNotesExceptMainFromUser(int $userId): array
    {
        $allNotes = $this->noteFinderRepository->findAllNotesExceptMainByUserId($userId);
        $this->setNotePrivilegeAndRemoveMessageOfHidden($allNotes);

        return $allNotes;
    }

    /**
     * Populate $privilege attribute of given NoteWithUserData array.
     *
     * @param NoteResultData[] $notes
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
            $clientOwnerId = $this->clientFinder->findClient($clientId)->userId;
        }

        foreach ($notes as $noteResultData) {
            // Privilege "only create" possible if user may not see the note but may create one
            $noteResultData->privilege = $this->notePrivilegeDeterminer->getNotePrivilege(
                (int)$noteResultData->userId,
                $clientOwnerId,
                $noteResultData->hidden,
                (bool)$noteResultData->deletedAt
            );
            // If not allowed to read
            if (!str_contains($noteResultData->privilege, 'R')) {
                // Change message of note to lorem ipsum
                $noteResultData->message = substr($randomText, 0, strlen($noteResultData->message ?? ''));
                // Remove line breaks and extra spaces from string
                $noteResultData->message = preg_replace('/\s\s+/', ' ', $noteResultData->message);
                // If user has no read right, set note to hidden
                $noteResultData->hidden = 1;
            }
        }
    }

    /**
     * Returns $notesAmount most recent notes.
     *
     * @param int $notesAmount
     *
     * @return NoteResultData[]
     */
    public function findMostRecentNotes(int $notesAmount = 10): array
    {
        $allNotes = $this->noteFinderRepository->findMostRecentNotes($notesAmount);
        $this->setNotePrivilegeAndRemoveMessageOfHidden($allNotes);

        return $allNotes;
    }

    /**
     * Return all notes except the main note that are linked to the given client.
     *
     * @param int $clientId
     *
     * @return NoteResultData[]
     */
    public function findAllNotesFromClientExceptMain(int $clientId): array
    {
        $allNotes = $this->noteFinderRepository->findAllNotesExceptMainWithUserByClientId($clientId);
        // In PHP, an object variable doesn't contain the object itself as value. It only contains an object identifier
        // meaning the reference is passed and changes are made on the original reference that can be used further
        // https://www.php.net/manual/en/language.oop5.references.php; https://stackoverflow.com/a/65805372/9013718
        $this->setNotePrivilegeAndRemoveMessageOfHidden($allNotes, null, $clientId);

        // Add client message as last note (it's always the "oldest" as it's the same age as the client entry itself)
        $clientData = $this->clientFinder->findClient($clientId, true);
        // The authorization for each note is verified, but the client message is added to the request
        // separately
        if (!empty($clientData->clientMessage)
            && $this->clientPermissionVerifier->isGrantedToRead($clientData->userId, $clientData->deletedAt)
        ) {
            $clientMessageNote = new NoteResultData();
            $clientMessageNote->message = $clientData->clientMessage;
            // The "userFullName" has to be the client itself as it's his client_message that is being displayed as note
            $clientMessageNote->userFullName = $clientData->firstName . ' ' . $clientData->lastName;
            $clientMessageNote->createdAt = $clientData->createdAt;
            // Always READ privilege as same as client read right and this request is for client read
            $clientMessageNote->privilege = Privilege::R->name;
            $clientMessageNote->isClientMessage = true;
            $allNotes[] = $clientMessageNote;
        }

        return $allNotes;
    }
}
