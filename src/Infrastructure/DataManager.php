<?php

namespace App\Infrastructure;

use App\Infrastructure\Exceptions\PersistenceRecordNotFoundException;
use Cake\Database\Connection;
use Cake\Database\Query;

abstract class DataManager
{

    private ?Connection $connection = null;
    protected ?string $table = null;

    public function __construct(Connection $connection = null)
    {
        $this->connection = $connection;
    }

    /**
     * Get a select query instance with target table already set
     *
     * @return Query
     */
    protected function newSelectQuery(): Query
    {
        return $this->connection->newQuery()->from($this->table);
    }

    /**
     * Get an insert query instance with target table already set
     *
     * @return Query
     */
    public function newInsertQuery(): Query
    {
        return $this->connection->newQuery()->into($this->table);
    }

    /**
     * Return all rows in a database
     * Reason why empty array is returned if nothing: https://stackoverflow.com/a/11536281/9013718
     *
     * @param array $fields
     * @param bool $withDeletedAtCheck with the condition that deleted_at IS NULL
     *
     * @return array
     */
    public function findAll(array $fields = ['*'], bool $withDeletedAtCheck = true): array
    {
        $deletedAtIsNull = true === $withDeletedAtCheck ? ['deleted_at IS' => null] : [];

        $query = $this->newSelectQuery();
        $query = $query->select($fields)->where($deletedAtIsNull);

        return $query->execute()->fetchAll('assoc') ?: [];
    }

    /**
     * Searches entry in table which has given id
     * If not found it returns an empty array
     *
     * @param string $id
     * @param array $fields
     * @param bool $withDeletedAtCheck with the condition that deleted_at IS NULL
     *
     * @return array
     */
    public function findById(string $id, array $fields = ['*'], bool $withDeletedAtCheck = true): array
    {
        $deletedAtIsNull = true === $withDeletedAtCheck ? ['deleted_at IS' => null] : [];

        $query = $this->newSelectQuery();
        $query = $query->select($fields)->where(array_merge($deletedAtIsNull, ['id' => $id]));

        return $query->execute()->fetch('assoc') ?: [];
    }

    /**
     * Searches entry in table with given value at given column
     *
     * @param string $column
     * @param mixed $value
     * @param array $fields
     * @param bool $withDeletedAtCheck with the condition that deleted_at IS NULL
     *
     * @return array first match to given condition
     */
    public function findOneBy(string $column, mixed $value, array $fields = ['*'], bool $withDeletedAtCheck = true): array
    {
        $deletedAtIsNull = true === $withDeletedAtCheck ? ['deleted_at IS' => null] : [];

        $query = $this->newSelectQuery();
        // Retrieving a single Row
        $query = $query->select($fields)->andWhere(array_merge($deletedAtIsNull, [$column => $value]));
        // return first entry from result with fetch
        // "?:" returns the value on the left only if it is set and truthy (if not set it gives a notice)
        return $query->execute()->fetch('assoc') ?: [];
    }

    /**
     * Searches one entry in table with given where conditions
     *
     * @param string $where ['column' => value, 'otherColumn' => value] (test against null -> ['column IS' => null,])
     * deleted_at is already in the where clause as a default
     * @param array $fields
     * @param bool $withDeletedAtCheck with the condition that deleted_at IS NULL
     *
     * @return array
     */
    public function findOneWhere(string $where, array $fields = ['*'], bool $withDeletedAtCheck = true): array
    {
        $deletedAtIsNull = true === $withDeletedAtCheck ? ['deleted_at IS' => null] : [];

        $query = $this->newSelectQuery();
        // retrieving rows
        $query = $query->select($fields)->andWhere(array_merge($deletedAtIsNull, $where));
        // return first entry from result with fetch
        return $query->execute()->fetch('assoc') ?: [];
    }

    /**
     * Searches entry in table with given value at given column
     *
     * @param string $column
     * @param mixed $value
     * @param array $fields
     * @param bool $withDeletedAtCheck with the condition that deleted_at IS NULL
     *
     * @return array
     */
    public function findAllBy(string $column, mixed $value, array $fields = ['*'], bool $withDeletedAtCheck = true): array
    {
        $deletedAtIsNull = true === $withDeletedAtCheck ? ['deleted_at IS' => null] : [];

        $query = $this->newSelectQuery();
        // retrieving rows
        $query = $query->select($fields)->andWhere(array_merge($deletedAtIsNull, [$column => $value]));

        return $query->execute()->fetchAll('assoc') ?: [];
    }

    /**
     * Searches all entries in table matching given where conditions
     *
     * @param string $where ['column' => value, 'otherColumn' => value] (test against null -> ['column IS' => null,])
     * deleted_at is already in the where clause as a default
     * @param array $fields
     * @param bool $withDeletedAtCheck with the condition that deleted_at IS NULL
     *
     * @return array
     */
    public function findAllWhere(string $where, array $fields = ['*'], bool $withDeletedAtCheck = true): array
    {
        $deletedAtIsNull = true === $withDeletedAtCheck ? ['deleted_at IS' => null] : [];

        $query = $this->newSelectQuery();
        // retrieving rows
        $query = $query->select($fields)->andWhere(array_merge($deletedAtIsNull, $where));

        return $query->execute()->fetchAll('assoc') ?: [];
    }

    /**
     * Retrieve entry from table which has given id
     * If it doesn't find anything an error is thrown
     *
     * @param int $id
     * @param array $fields
     * @param bool $withDeletedAtCheck with the condition that deleted_at IS NULL
     *
     * @return array
     * @throws PersistenceRecordNotFoundException
     */
    public function getById(int $id, array $fields = ['*'], bool $withDeletedAtCheck = true): array
    {
        $deletedAtIsNull = true === $withDeletedAtCheck ? ['deleted_at IS' => null] : [];

        $query = $this->newSelectQuery();
        $query = $query->select($fields)->where(array_merge($deletedAtIsNull, ['id' => $id]));
        $entry = $query->execute()->fetch('assoc');
        if (!$entry) {
            $err = new PersistenceRecordNotFoundException();
            $err->setNotFoundElement($this->table);
            throw $err;
        }
        return $entry;
    }




    /**
     * Insert in database.
     *
     * @param array $row with data to insert
     * @return string
     */
    public function insert(array $row): string
    {
        return $this->connection->insert($this->table, $row)->lastInsertId();
    }

    /**
     * Update database
     * Data is an assoc array of columns to change
     * Example: ['name' => 'New name']
     *
     * @param array $data
     * @param string|int $whereId
     * @return bool
     */
    protected function update(array $data, int|string $whereId): bool
    {
        $query = $this->connection->newQuery();
        $query->update($this->table)->set($data)->where(
            [
                'id' => $whereId
            ]
        );
        return $query->execute()->rowCount() > 0;
    }

    /**
     * Delete from database permanently (execute DELETE statement)
     *
     * @param string|int $id
     *
     * @return bool
     */
    protected function hardDelete(string|int $id): bool
    {
        return $this->connection->delete($this->table, ['id' => $id])->rowCount() > 0;
    }

    /**
     * Soft delete entry with given id from database
     *
     * @param string|int $id
     * @return bool
     */
    public function delete(string|int $id): bool
    {
        $query = $this->connection->newQuery();
        $query->update($this->table)->set(['deleted_at' => date('Y-m-d H:i:s')])->where(['id' => $id]);
        return $query->execute()->rowCount() > 0;
    }

    /**
     * Soft delete entry matching given arguments
     *
     * @param array $conditions assoc array of where conditions
     * Example: ['tbl_id' => 2, 'name' => 'nameToDelete']
     * @return bool
     */
    public function deleteWhere(array $conditions): bool
    {
        $query = $this->connection->newQuery();
        $query->update($this->table)->set(['deleted_at' => date('Y-m-d H:i:s')])->where([$conditions]);
        return $query->execute()->rowCount() > 0;
    }

    /**
     * Check if value exists in database
     *
     * @param string $column
     * @param mixed $value
     * @return bool
     */
    public function exists(string $column, mixed $value): bool
    {
        $query = $this->newSelectQuery();
        $query->select(1)->where([$column => $value]);
        $row = $query->execute()->fetch();
        return !empty($row);
    }

    /**
     * Find with query
     * For joins and other complex queries
     * https://book.cakephp.org/3/en/orm/query-builder.html#adding-joins
     *
     * @param Query $query
     * @return array
     */
    public function findByQuery(Query $query): array
    {
        return $query->execute()->fetchAll('assoc') ?: [];
    }

}
