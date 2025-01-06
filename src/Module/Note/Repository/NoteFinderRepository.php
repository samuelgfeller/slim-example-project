<?php

namespace App\Module\Note\Repository;

use App\Core\Infrastructure\Factory\QueryFactory;
use App\Core\Infrastructure\Utility\Hydrator;
use App\Module\Note\Data\NoteData;
use App\Module\Note\Data\NoteResultData;

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
        'deleted_at' => 'note.deleted_at',
        'user_id' => 'note.user_id',
    ];

    public function __construct(
        private readonly QueryFactory $queryFactory,
        private readonly Hydrator $hydrator,
    ) {
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
        $query = $this->queryFactory->selectQuery()->select(['*'])->from('note')->where(
            ['deleted_at IS' => null, 'id' => $id]
        );
        $noteRow = $query->execute()->fetch('assoc') ?: [];

        return new NoteData($noteRow);
    }

    /**
     * Return all notes which are linked to the given user except the main note.
     *
     * @param int $userId
     *
     * @return NoteResultData[]
     */
    public function findAllNotesExceptMainByUserId(int $userId): array
    {
        $query = $this->queryFactory->selectQuery()->from('note');

        $concatName = $query->func()->concat(['user.first_name' => 'identifier', ' ', 'user.last_name' => 'identifier']);

        $query->select(array_merge($this->noteResultFields, ['user_full_name' => $concatName]))
            ->join(['table' => 'user', 'conditions' => 'note.user_id = user.id'])
            ->andWhere([
                // Not unsafe as it's not an expression and thus escaped by querybuilder
                'note.user_id' => $userId,
                'note.is_main' => 0,
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
        $query = $this->queryFactory->selectQuery()->from('note');

        $concatName = $query->func()->concat([
            $query->func()->coalesce(['user.first_name' => 'identifier', '']),
            ' ',
            $query->func()->coalesce(['user.last_name' => 'identifier', '']),
        ]);

        $query->select(array_merge($this->noteResultFields, ['user_full_name' => $concatName]))
            ->join(['user' => ['table' => 'user', 'type' => 'LEFT', 'conditions' => 'note.user_id = user.id']])
            ->join(['client' => ['table' => 'client', 'type' => 'LEFT', 'conditions' => 'note.client_id = client.id']])
            ->where(
                [
                    // Not unsafe as it's not an expression and thus escaped by querybuilder
                    'note.client_id' => $clientId,
                    'note.is_main' => 0,
                    'OR' => [
                        'note.deleted_at IS' => null,
                        'note.deleted_at' => $query->identifier('client.deleted_at'),
                    ],
                ]
            )->orderByDesc('note.created_at');
        $resultRows = $query->execute()->fetchAll('assoc') ?: [];

        // Convert to list of Note objects with associated User info
        return $this->hydrator->hydrate($resultRows, NoteResultData::class);
    }

    /**
     * Returns given amount of notes ordered by most recent.
     *
     * @param int $notesAmount
     *
     * @return NoteResultData[]
     */
    public function findMostRecentNotes(int $notesAmount): array
    {
        $query = $this->queryFactory->selectQuery()->from('note');

        $concatUserName = $query->func()->concat(
            // Cake interprets the string literally with "literal", so IFNULL() and column are interpreted as raw sql
            ['IFNULL(user.first_name, "")' => 'literal', ' ', 'IFNULL(user.last_name, "")' => 'identifier']
        );
        $concatClientName = $query->func()->concat(
            ['IFNULL(client.first_name, "")' => 'literal', ' ', 'IFNULL(client.last_name, "")' => 'identifier']
        );

        $query->select(
            array_merge($this->noteResultFields, [
                'user_full_name' => $concatUserName,
                'client_full_name' => $concatClientName,
            ])
        )
            ->join(['table' => 'user', 'conditions' => 'note.user_id = user.id'])
            ->leftJoin('client', ['note.client_id = client.id'])
            ->andWhere(['note.deleted_at IS' => null, 'note.is_main' => 0])
            ->orderBy(['note.updated_at' => 'DESC', 'note.created_at' => 'DESC'])->limit($notesAmount);

        $resultRows = $query->execute()->fetchAll('assoc') ?: [];

        // Convert to list of Note objects with associated User info
        return $this->hydrator->hydrate($resultRows, NoteResultData::class);
    }

    /**
     * Find the amount of notes without counting the main note.
     *
     * @param int $clientId
     *
     * @return int
     */
    public function findClientNotesAmount(int $clientId): int
    {
        $query = $this->queryFactory->selectQuery()->from('client');

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
