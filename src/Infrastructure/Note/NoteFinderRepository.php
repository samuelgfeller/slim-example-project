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
     * EXCEPT the main note which is handled differently
     *
     * @param int $clientId
     * @param bool $orderDesc
     * @return NoteWithUserData[]
     */
    public function findAllNotesExceptMainWithUserByClientId(int $clientId, bool $orderDesc = false): array
    {
        $query = $this->queryFactory->newQuery()->from('note');

        $concatName = $query->func()->concat(['user.first_name' => 'identifier', ' ', 'user.surname' => 'identifier']);

        $query->select(
            [
                'client_main_note' => 'client.main_note_id',
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
            'client' => ['table' => 'client', 'type' => 'LEFT', 'conditions' => 'note.client_id = client.id']
        ])
            ->where([
                    // Not unsafe as it's not an expression and thus escaped by querybuilder
                    'note.client_id' => $clientId,
                    // We have to tell the query builder when the string is literal or if it's a column, it doesn't
                    // detect that alone. https://discourse.cakephp.org/t/query-builder-documentation-examples-without-orm/10471
                    'OR' => [
                        'note.id <>' => $query->identifier('client.main_note_id'),
                        'client.main_note_id IS' => null
                    ],
                    'note.deleted_at IS' => null
                ]
            )->orderDesc('note.created_at');
        $resultRows = $query->execute()->fetchAll('assoc') ?: [];
        // Convert to list of Note objects with associated User info
        return $this->hydrator->hydrate($resultRows, NoteWithUserData::class);
    }


}