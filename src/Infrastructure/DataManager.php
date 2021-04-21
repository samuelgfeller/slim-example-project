<?php

namespace App\Infrastructure;

use App\Infrastructure\Exceptions\PersistenceRecordNotFoundException;
use Cake\Database\Connection;
use Cake\Database\Query;

final class DataManager
{

    /**
     * DataManager constructor.
     *
     * @param Connection $connection
     */
    public function __construct(private Connection $connection)
    {
    }

    /**
     * -----
     * Below is the query factory
     * -----
     */

    /**
     * Get a query instance
     * ! Dont forget deleted_at when selecting or mass updating
     *
     * SELECT Example:
     *     $query = $this->dataManager->newQuery()->select(['*'])->from('user')->where(
     *         ['deleted_at IS' => null, 'name LIKE' => '%John%']);
     *     return $query->execute()->fetchAll('assoc');
     * UPDATE Example:
     *     $query = $this->dataManager->newQuery()->update('user')->set($data)->where(['id' => 1]);
     *     return $query->execute()->rowCount() > 0;
     *
     * @return Query
     */
    public function newQuery(): Query
    {
        return $this->connection->newQuery();
    }

    /**
     * Data is an assoc array of rows to insert where the key is the column name
     * Example:
     *     return (int)$this->dataManager->newInsert($data)->into('user')->execute()->lastInsertId();
     *
     * @param array $data ['col_name' => 'Value', 'other_col' => 'Other value']
     * @return Query
     */
    public function newInsert(array $data): Query
    {
        return $this->connection->newQuery()->insert(array_keys($data))->values($data);
    }

    /**
     * Soft delete entry with given id from database
     * Table name needed here as its a required argument for update() function
     * Example:
     *     $query = $this->dataManager->newDelete('post')->where(['id' => $id]);
     *     return $query->execute()->rowCount() > 0;
     *
     * @param string $fromTable
     * @return Query
     */
    public function newDelete(string $fromTable): Query
    {
        return $this->connection->newQuery()->update($fromTable)->set(['deleted_at' => date('Y-m-d H:i:s')]);
    }


    /**
     * -----
     * Below helper function also serve as reminder to always take into account the soft delete
     * `deleted_at` column and prevent bugs related to that (because it gets forgotten VERY easily)
     * -----
     */

    /**
     * Return all rows in a database
     * Reason why empty array is returned if nothing: https://stackoverflow.com/a/11536281/9013718
     *
     * @param string $fromTable
     * @param array $fields
     * @param bool $withDeletedAtCheck with the condition that deleted_at IS NULL
     *
     * @return array
     */
    public function findAll(string $fromTable, array $fields = ['*'], bool $withDeletedAtCheck = true): array
    {
        $deletedAtIsNull = true === $withDeletedAtCheck ? ['deleted_at IS' => null] : [];

        $query = $this->newQuery()->from($fromTable);
        $query = $query->select($fields)->where($deletedAtIsNull);

        return $query->execute()->fetchAll('assoc') ?: [];
    }

    /**
     * Searches entry in table which has given id
     * If not found it returns an empty array
     *
     * @param string $fromTable
     * @param string|int $id
     * @param array $fields
     * @param bool $withDeletedAtCheck with the condition that deleted_at IS NULL
     *
     * @return array
     */
    public function findById(
        string $fromTable,
        string|int $id,
        array $fields = ['*'],
        bool $withDeletedAtCheck = true
    ): array {
        $deletedAtIsNull = true === $withDeletedAtCheck ? ['deleted_at IS' => null] : [];

        $query = $this->newQuery()->from($fromTable);
        $query = $query->select($fields)->where(array_merge($deletedAtIsNull, ['id' => $id]));

        return $query->execute()->fetch('assoc') ?: [];
    }

    /**
     * Searches entry in table with given value at given column
     *
     * @param string $fromTable
     * @param string $column
     * @param mixed $value
     * @param array $fields
     * @param bool $withDeletedAtCheck with the condition that deleted_at IS NULL
     *
     * @return array first match to given condition
     */
    public function findOneBy(
        string $fromTable,
        string $column,
        mixed $value,
        array $fields = ['*'],
        bool $withDeletedAtCheck = true
    ): array {
        $deletedAtIsNull = true === $withDeletedAtCheck ? ['deleted_at IS' => null] : [];

        $query = $this->newQuery()->from($fromTable);
        // Retrieving a single Row
        $query = $query->select($fields)->andWhere(array_merge($deletedAtIsNull, [$column => $value]));

        // return first entry from result with fetch
        // "?:" returns the value on the left only if it is set and truthy (if not set it gives a notice)
        return $query->execute()->fetch('assoc') ?: [];
    }

    /**
     * Searches one entry in table with given where conditions
     *
     * @param string $fromTable
     * @param string $where ['column' => value, 'otherColumn' => value] (test against null -> ['column IS' => null,])
     * deleted_at is already in the where clause as a default
     * @param array $fields
     * @param bool $withDeletedAtCheck with the condition that deleted_at IS NULL
     *
     * @return array
     */
    public function findOneWhere(
        string $fromTable,
        string $where,
        array $fields = ['*'],
        bool $withDeletedAtCheck = true
    ): array {
        $deletedAtIsNull = true === $withDeletedAtCheck ? ['deleted_at IS' => null] : [];

        $query = $this->newQuery()->from($fromTable);
        // retrieving rows
        $query = $query->select($fields)->andWhere(array_merge($deletedAtIsNull, $where));
        // return first entry from result with fetch
        return $query->execute()->fetch('assoc') ?: [];
    }

    /**
     * Searches entry in table with given value at given column
     *
     * @param string $fromTable
     * @param string $column
     * @param mixed $value
     * @param array $fields
     * @param bool $withDeletedAtCheck with the condition that deleted_at IS NULL
     *
     * @return array
     */
    public function findAllBy(
        string $fromTable,
        string $column,
        mixed $value,
        array $fields = ['*'],
        bool $withDeletedAtCheck = true
    ): array {
        $deletedAtIsNull = true === $withDeletedAtCheck ? ['deleted_at IS' => null] : [];

        $query = $this->newQuery()->from($fromTable);
        // retrieving rows
        $query = $query->select($fields)->andWhere(array_merge($deletedAtIsNull, [$column => $value]));

        return $query->execute()->fetchAll('assoc') ?: [];
    }

    /**
     * Searches all entries in table matching given where conditions
     *
     * @param string $fromTable
     * @param string $where ['column' => value, 'otherColumn' => value] (test against null -> ['column IS' => null,])
     * deleted_at is already in the where clause as a default
     * @param array $fields
     * @param bool $withDeletedAtCheck with the condition that deleted_at IS NULL
     *
     * @return array
     */
    public function findAllWhere(
        string $fromTable,
        string $where,
        array $fields = ['*'],
        bool $withDeletedAtCheck = true
    ): array {
        $deletedAtIsNull = true === $withDeletedAtCheck ? ['deleted_at IS' => null] : [];

        $query = $this->newQuery()->from($fromTable);
        // retrieving rows
        $query = $query->select($fields)->andWhere(array_merge($deletedAtIsNull, $where));

        return $query->execute()->fetchAll('assoc') ?: [];
    }

    /**
     * Retrieve entry from table which has given id
     * If it doesn't find anything an error is thrown
     *
     * @param string $fromTable
     * @param int $id
     * @param array $fields
     * @param bool $withDeletedAtCheck with the condition that deleted_at IS NULL
     *
     * @return array
     * @throws PersistenceRecordNotFoundException
     */
    public function getById(string $fromTable, int $id, array $fields = ['*'], bool $withDeletedAtCheck = true): array
    {
        $deletedAtIsNull = true === $withDeletedAtCheck ? ['deleted_at IS' => null] : [];

        $query = $this->newQuery()->from($fromTable);
        $query = $query->select($fields)->where(array_merge($deletedAtIsNull, ['id' => $id]));
        $entry = $query->execute()->fetch('assoc');
        if (!$entry) {
            $err = new PersistenceRecordNotFoundException();
            $err->setNotFoundElement($fromTable);
            throw $err;
        }
        return $entry;
    }

    /**
     * Check if value exists in database
     *
     * @param string $fromTable
     * @param string $column
     * @param mixed $value
     * @return bool
     */
    public function exists(string $fromTable, string $column, mixed $value): bool
    {
        $query = $this->newQuery()->from($fromTable);
        $query->select(1)->where([$column => $value]);
        $row = $query->execute()->fetch();
        return !empty($row);
    }

    /**
     * Delete from database permanently (execute DELETE statement)
     *
     * @param string $fromTable
     * @param string|int $id
     *
     * @return bool
     */
    public function hardDelete(string $fromTable, string|int $id): bool
    {
        return $this->connection->delete($fromTable, ['id' => $id])->rowCount() > 0;
    }

}
