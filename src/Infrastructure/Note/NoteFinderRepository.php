<?php


namespace App\Infrastructure\Note;


use App\Common\Hydrator;
use App\Domain\Note\Data\NoteData;
use App\Domain\Note\Data\UserNoteData;
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
     * @return UserNoteData[]
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
                'user_name' => $concatName,
                'user_email' => 'user.email',
                'user_role' => 'user.role',
            ]
        )->join(['table' => 'user', 'conditions' => 'note.user_id = user.id'])->andWhere(
            ['note.deleted_at IS' => null]
        );
        $resultRows = $query->execute()->fetchAll('assoc') ?: [];
        // Convert to list of Note objects with associated User info
        return $this->hydrator->hydrate($resultRows, UserNoteData::class);
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
     * @return UserNoteData
     */
    public function findUserNoteById(int $id): UserNoteData
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
                'user_name' => $concatName,
                'user_role' => 'user.role',
            ]
        )->join(['table' => 'user', 'conditions' => 'note.user_id = user.id'])->andWhere(
            ['note.id' => $id, 'note.deleted_at IS' => null]
        );
        $resultRows = $query->execute()->fetch('assoc') ?: [];
        // Instantiate UserNote DTO
        return new UserNoteData($resultRows);
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
     * @return UserNoteData[]
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
                'user_name' => $concatName,
                'user_role' => 'user.role',
            ]
        )->join(['table' => 'user', 'conditions' => 'note.user_id = user.id'])->andWhere(
            [
                'note.user_id' => $userId, // Not unsafe as its not an expression and thus escaped by querybuilder
                'note.deleted_at IS' => null
            ]
        );
        $resultRows = $query->execute()->fetchAll('assoc') ?: [];
        // Convert to list of Note objects with associated User info
        return $this->hydrator->hydrate($resultRows, UserNoteData::class);
    }
}