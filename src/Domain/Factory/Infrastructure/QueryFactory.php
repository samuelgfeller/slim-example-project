<?php

namespace App\Domain\Factory\Infrastructure;

use Cake\Database\Connection;
use Cake\Database\Query;
use Cake\Database\Query\InsertQuery;
use Cake\Database\Query\SelectQuery;
use Cake\Database\Query\UpdateQuery;

/**
 * Factory.
 */
final class QueryFactory
{
    public function __construct(public readonly Connection $connection)
    {
    }

    /**
     * Returns a select query instance.
     ** Don't forget to exclude deleted_at records.
     *
     * SELECT Example:
     *     $query = $this->queryFactory->selectQuery()->select(['*'])->from('user')->where(
     *         ['deleted_at IS' => null, 'name LIKE' => '%John%']);
     *     return $query->execute()->fetchAll('assoc');
     *
     * @return SelectQuery
     */
    public function selectQuery(): \Cake\Database\Query\SelectQuery
    {
        return $this->connection->selectQuery();
    }

    /**
     * Returns an update query instance.
     ** Don't forget to take into account deleted_at records.
     *
     * UPDATE Example:
     *     $query = $this->queryFactory->updateQuery()->update('user')->set($data)->where(['id' => 1]);
     *     return $query->execute()->rowCount() > 0;
     *
     * @return UpdateQuery
     */
    public function updateQuery(): UpdateQuery
    {
        return $this->connection->updateQuery();
    }

    /**
     * Returns an insert query instance.
     *
     * @return InsertQuery the insert query object
     */
    public function insertQuery(): InsertQuery
    {
        return $this->connection->insertQuery();
    }

    /**
     * Data is an assoc array of a row to insert where the key is the column name.
     *
     * Usage:
     *     return (int)$this->queryFactory->insertQueryWithData($data)->into('user')->execute()->lastInsertId();.
     *
     * @param array $data ['col_name' => 'Value', 'other_col' => 'Other value']
     *
     * @return InsertQuery
     */
    public function insertQueryWithData(array $data): InsertQuery
    {
        return $this->connection->insertQuery()->insert(array_keys($data))->values($data);
    }

    /**
     * Soft deletes entry from given table name.
     *
     * Usage:
     *     $query = $this->queryFactory->softDeleteQuery('user')->where(['id' => $id]);
     *     return $query->execute()->rowCount() > 0;.
     *
     * @param string $fromTable
     *
     * @return UpdateQuery
     */
    public function softDeleteQuery(string $fromTable): UpdateQuery
    {
        return $this->connection->updateQuery()->update($fromTable)->set(['deleted_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Returns a delete query instance for hard deletion.
     *
     * @return Query\DeleteQuery the delete query object
     */
    public function hardDeleteQuery(): Query\DeleteQuery
    {
        // Return the delete query object created by the connection.
        return $this->connection->deleteQuery();
    }

    /**
     * Data is an assoc array of rows to insert where the key is the column name
     * Example:
     *     return (int)$this->queryFactory->newMultipleInsert($data)->into('user')->execute()->lastInsertId();.
     *
     * @param array $arrayOfData [['col_name' => 'Value', 'other_col' => 'Other value'], ['col_name' => 'value']]
     *
     * @return InsertQuery
     */
    public function insertQueryMultipleRows(array $arrayOfData): InsertQuery
    {
        $query = $this->connection->insertQuery()->insert(array_keys($arrayOfData[array_key_first($arrayOfData)]));
        // According to the docs, chaining ->values is the way to go https://book.cakephp.org/4/en/orm/query-builder.html#inserting-data
        foreach ($arrayOfData as $data) {
            $query->values($data);
        }

        return $query;
    }
}
