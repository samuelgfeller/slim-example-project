<?php

namespace App\Infrastructure\Note;

use App\Common\Hydrator;
use App\Domain\Note\Data\NoteData;
use App\Domain\Note\Data\NoteResultData;
use App\Infrastructure\Exceptions\PersistenceRecordNotFoundException;
use App\Infrastructure\Factory\QueryFactory;

class NoteFinderRepository
{
    /** Fields for queries that populate @see NoteResultData */
    public array $noteResultFields = [
        'id' => 'note.id',
        'client_id' => 'note.client_id',
        'message' => 'note.message',
        'hidden' => 'note.hidden',
        'updated_at' => 'note.updated_at',
        'created_at' => 'note.created_at',
        'user_id' => 'note.user_id',
    ];

    public function __construct(
        private readonly QueryFactory $queryFactory,
        private readonly Hydrator $hydrator
    ) {
    }

    /**
     * Return all notes with users attribute loaded.
     *
     * @return NoteResultData[]
     */
    public function findAllNotesWithUsers(): array
    {
        $query = $this->queryFactory->newQuery()->from('note');
        $concatName = $query->func()->concat(['user.first_name' => 'identifier', ' ', 'user.surname' => 'identifier']);
        $query->select(array_merge($this->noteResultFields, ['user_full_name' => $concatName]))
            ->join(['table' => 'user', 'conditions' => 'note.user_id = user.id'])
            ->andWhere(['note.deleted_at IS' => null]);
        $resultRows = $query->execute()->fetchAll('assoc') ?: [];
        // Convert to list of Note objects with associated User info
        return $this->hydrator->hydrate($resultRows, NoteResultData::class);
    }

    /**
     * Return note with given id if it exists
     * otherwise null.
     *
     * @param string|int $id
     *
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
     * Return all notes with users attribute loaded.
     *
     * @param int $id
     *
     * @throws \Exception
     *
     * @return NoteResultData
     */
    public function findNoteWithUserById(int $id): NoteResultData
    {
        $query = $this->queryFactory->newQuery()->from('note');

        $concatName = $query->func()->concat(['user.first_name' => 'identifier', ' ', 'user.surname' => 'identifier']);

        $query->select(array_merge($this->noteResultFields, ['user_full_name' => $concatName]))
            ->join(['table' => 'user', 'conditions' => 'note.user_id = user.id'])
            ->andWhere(['note.id' => $id, 'note.deleted_at IS' => null]);
        $resultRows = $query->execute()->fetch('assoc') ?: [];
        // Instantiate UserNote DTO
        return new NoteResultData($resultRows);
    }

    /**
     * Retrieve note from database
     * If not found error is thrown.
     *
     * @param int $id
     *
     * @throws PersistenceRecordNotFoundException
     *
     * @return array
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
     * Return all notes which are linked to the given user.
     *
     * @param int $userId
     *
     * @return NoteResultData[]
     */
    public function findAllNotesByUserId(int $userId): array
    {
        $query = $this->queryFactory->newQuery()->from('note');

        $concatName = $query->func()->concat(['user.first_name' => 'identifier', ' ', 'user.surname' => 'identifier']);

        $query->select(array_merge($this->noteResultFields, ['user_full_name' => $concatName]))
            ->join(['table' => 'user', 'conditions' => 'note.user_id = user.id'])
            ->andWhere([
                // Not unsafe as it's not an expression and thus escaped by querybuilder
                'note.user_id' => $userId,
                'note.deleted_at IS' => null,
            ]);
        $resultRows = $query->execute()->fetchAll('assoc') ?: [];
        // Convert to list of Note objects with associated User info
        return $this->hydrator->hydrate($resultRows, NoteResultData::class);
    }

    /**
     * Return all notes which are linked to the given client
     * from most recent to oldest EXCEPT for the main note.
     *
     * @param int $clientId
     *
     * @return NoteResultData[]
     */
    public function findAllNotesExceptMainWithUserByClientId(int $clientId): array
    {
        $query = $this->queryFactory->newQuery()->from('note');

        $concatName = $query->func()->concat([
            $query->func()->coalesce(['user.first_name' => 'identifier', '']),
            ' ',
            $query->func()->coalesce(['user.surname' => 'identifier', '']),
        ]);

        $query->select(array_merge($this->noteResultFields, ['user_full_name' => $concatName]))
            ->join(['user' => ['table' => 'user', 'type' => 'LEFT', 'conditions' => 'note.user_id = user.id']])
            ->where(
                [
                    // Not unsafe as it's not an expression and thus escaped by querybuilder
                    'note.client_id' => $clientId,
                    'note.is_main' => 0,
                    'note.deleted_at IS' => null,
                ]
            )->orderDesc('note.created_at');
        $resultRows = $query->execute()->fetchAll('assoc') ?: [];
        // Convert to list of Note objects with associated User info
        return $this->hydrator->hydrate($resultRows, NoteResultData::class);
    }

    /**
     * Return all notes which are linked to the given user.
     *
     * @param int $notesAmount
     *
     * @return NoteResultData[]
     */
    public function findMostRecentNotes(int $notesAmount): array
    {
        $query = $this->queryFactory->newQuery()->from('note');

        $concatUserName = $query->func()->concat(
            ['user.first_name' => 'identifier', ' ', 'user.surname' => 'identifier']
        );
        $concatClientName = $query->func()->concat(
            ['client.first_name' => 'identifier', ' ', 'client.last_name' => 'identifier']
        );

        $query->select(
            array_merge($this->noteResultFields, [
                'user_full_name' => $concatUserName,
                'client_full_name' => $concatClientName,
            ])
        )
            ->join(['table' => 'user', 'conditions' => 'note.user_id = user.id'])
            ->leftJoin('client', ['note.client_id = client.id'])
            ->andWhere(['note.deleted_at IS' => null])
            ->orderDesc('note.updated_at')->limit($notesAmount);
        $resultRows = $query->execute()->fetchAll('assoc') ?: [];
        // Convert to list of Note objects with associated User info
        return $this->hydrator->hydrate($resultRows, NoteResultData::class);
    }

    /**
     * Find amount of notes without counting the main note.
     *
     * @param int $clientId
     *
     * @return int
     */
    public function findClientNotesAmount(int $clientId): int
    {
        $query = $this->queryFactory->newQuery()->from('client');

        $query->select(['amount' => $query->func()->count('n.id')])
            ->join(['n' => ['table' => 'note', 'type' => 'LEFT', 'conditions' => 'n.client_id = client.id']])
            ->where(
                [
                    'client.id' => $clientId,
                    // The main note should not be counted in
                    'n.is_main' => 0,
                    'n.deleted_at IS' => null,
                ]
            );
        // Return amount of notes
        return (int)$query->execute()->fetch('assoc')['amount'];
    }
}
