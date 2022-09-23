<?php


namespace App\Infrastructure\Note;


use App\Common\Hydrator;
use App\Domain\Note\Data\NoteData;
use App\Domain\Note\Data\NoteWithUserData;
use App\Infrastructure\Exceptions\PersistenceRecordNotFoundException;
use App\Infrastructure\Factory\QueryFactory;

class NoteFinderRepository
{

    public function __construct(
        private QueryFactory $queryFactory,
        private Hydrator $hydrator
    ) {
    }

    /**
     * Return all notes with users attribute loaded
     *
     * @return NoteWithUserData[]
     */
    public function findAllNotesWithUsers(): array
    {
        $query = $this->queryFactory->newQuery()->from('note');
        $concatName = $query->func()->concat(['user.first_name' => 'identifier', ' ', 'user.surname' => 'identifier']);
        $query->select(
            [
                'note_id' => 'note.id',
                'user_id' => 'user.id',
                'note_message' => 'note.message',
                'note_created_at' => 'note.created_at',
                'note_updated_at' => 'note.updated_at',
                'user_full_name' => $concatName,
                'user_email' => 'user.email',
                'user_role' => 'user.role',
            ]
        )->join(['table' => 'user', 'conditions' => 'note.user_id = user.id'])->andWhere(
            ['note.deleted_at IS' => null]
        );
        $resultRows = $query->execute()->fetchAll('assoc') ?: [];
        // Convert to list of Note objects with associated User info
        return $this->hydrator->hydrate($resultRows, NoteWithUserData::class);
    }

    /**
     * Return note with given id if it exists
     * otherwise null
     *
     * @param string|int $id
     * @return NoteData
     */
    public function findNoteById(string|int $id): NoteData
    {
        $query = $this->queryFactory->newQuery()->select(['*'])->from('note')->where(
            ['deleted_at IS' => null, 'id' => $id]
        );
        $noteRow = $query->execute()->fetch('assoc') ?: [];
        return new NoteData($noteRow);
    }

    /**
     * Return all notes with users attribute loaded
     *
     * @param int $id
     * @return NoteWithUserData
     */
    public function findNoteWithUserById(int $id): NoteWithUserData
    {
        $query = $this->queryFactory->newQuery()->from('note');

        $concatName = $query->func()->concat(['user.first_name' => 'identifier', ' ', 'user.surname' => 'identifier']);

        $query->select(
            [
                'note_id' => 'note.id',
                'user_id' => 'user.id',
                'note_message' => 'note.message',
                'note_created_at' => 'note.created_at',
                'note_updated_at' => 'note.updated_at',
                'user_full_name' => $concatName,
                'user_role' => 'user.role',
            ]
        )->join(['table' => 'user', 'conditions' => 'note.user_id = user.id'])->andWhere(
            ['note.id' => $id, 'note.deleted_at IS' => null]
        );
        $resultRows = $query->execute()->fetch('assoc') ?: [];
        // Instantiate UserNote DTO
        return new NoteWithUserData($resultRows);
    }


    /**
     * Retrieve note from database
     * If not found error is thrown
     *
     * @param int $id
     * @return array
     * @throws PersistenceRecordNotFoundException
     */
    public function getNoteById(int $id): array
    {
        $query = $this->queryFactory->newQuery()->select(['*'])->from('note')->where(
            ['deleted_at IS' => null, 'id' => $id]
        );
        $entry = $query->execute()->fetch('assoc');
        if (!$entry) {
            throw new PersistenceRecordNotFoundException('note');
        }
        return $entry;
    }

    /**
     * Return all notes which are linked to the given user
     *
     * @param int $userId
     * @return NoteWithUserData[]
     */
    public function findAllNotesByUserId(int $userId): array
    {
        $query = $this->queryFactory->newQuery()->from('note');

        $concatName = $query->func()->concat(['user.first_name' => 'identifier', ' ', 'user.surname' => 'identifier']);

        $query->select(
            [
                'note_id' => 'note.id',
                'user_id' => 'user.id',
                'note_message' => 'note.message',
                'note_created_at' => 'note.created_at',
                'note_updated_at' => 'note.updated_at',
                'user_full_name' => $concatName,
                'user_role' => 'user.role',
            ]
        )->join(['table' => 'user', 'conditions' => 'note.user_id = user.id'])->andWhere(
            [
                'note.user_id' => $userId, // Not unsafe as it's not an expression and thus escaped by querybuilder
                'note.deleted_at IS' => null
            ]
        );
        $resultRows = $query->execute()->fetchAll('assoc') ?: [];
        // Convert to list of Note objects with associated User info
        return $this->hydrator->hydrate($resultRows, NoteWithUserData::class);
    }

    /**
     * Return all notes which are linked to the given client
     * from most recent to oldest EXCEPT for the main note
     *
     * @param int $clientId
     * @return NoteWithUserData[]
     */
    public function findAllNotesExceptMainWithUserByClientId(int $clientId): array
    {
        $query = $this->queryFactory->newQuery()->from('note');

        $concatName = $query->func()->concat(['user.first_name' => 'identifier', ' ', 'user.surname' => 'identifier']);

        $query->select(
            [
                'note_id' => 'note.id',
                'user_id' => 'user.id',
                'note_message' => 'note.message',
                'note_created_at' => 'note.created_at',
                'note_updated_at' => 'note.updated_at',
                'user_full_name' => $concatName,
                'user_role' => 'user.role',
            ]
        )->join([
            'user' => ['table' => 'user', 'type' => 'LEFT', 'conditions' => 'note.user_id = user.id'],
        ])
            ->where([
                    // Not unsafe as it's not an expression and thus escaped by querybuilder
                    'note.client_id' => $clientId,
                    'note.is_main' => 0,
                    'note.deleted_at IS' => null
                ]
            )->orderDesc('note.created_at');
        $resultRows = $query->execute()->fetchAll('assoc') ?: [];
        // Convert to list of Note objects with associated User info
        return $this->hydrator->hydrate($resultRows, NoteWithUserData::class);
    }

    /**
     * Find amount of notes without counting the main note
     *
     * @param int $clientId
     * @return int
     */
    public function findClientNotesAmount(int $clientId): int
    {
        $query = $this->queryFactory->newQuery()->from('client');

        $query->select(
            [
                'amount' => $query->func()->count('n.id'),
            ]
        )->join([
            'n' => ['table' => 'note', 'type' => 'LEFT', 'conditions' => 'n.client_id = client.id'],
        ])
            ->where([
                    'client.id' => $clientId,
                    // The main note should not be counted in
                    'n.is_main' => 0,
                    'n.deleted_at IS' => null
                ]
            );
        // Return amount of notes
        $amount = (int)$query->execute()->fetch('assoc')['amount'];
        return $amount;
    }

}