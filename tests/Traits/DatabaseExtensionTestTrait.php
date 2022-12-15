<?php

namespace App\Test\Traits;

use PDO;
use Selective\TestTrait\Traits\DatabaseTestTrait;

/**
 * Slim App Route Test Trait.
 */
trait DatabaseExtensionTestTrait
{
    use DatabaseTestTrait;

    /**
     * Fetch rows by given column.
     *
     * @param string $table Table name
     * @param string $whereColumn The column name of the select query
     * @param mixed $whereValue The value that will be searched for
     * @param array|null $fields The array of fields
     *
     * @return array[] array or rows
     */
    protected function findTableRowsByColumn(
        string $table,
        string $whereColumn,
        mixed $whereValue,
        array $fields = null
    ): array {
        $sql = sprintf('SELECT * FROM `%s` WHERE `%s` = :whereValue', $table, $whereColumn);
        $statement = $this->createPreparedStatement($sql);
        $statement->execute(['whereValue' => $whereValue]);

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        $rowsWithFilteredFields = null;
        if ($fields) {
            foreach ($rows as $row) {
                $rowsWithFilteredFields[] = array_intersect_key($row, array_flip($fields));
            }
        }

        return $rowsWithFilteredFields ?? $rows;
    }

    /**
     * Fetch rows by given where array.
     *
     * @param string $table Table name
     * @param string $whereString
     * @param array|null $fields The array of fields
     *
     * @return array[] array or rows
     */
    protected function findTableRowsWhere(
        string $table,
        string $whereString,
        array $fields = null
    ): array {
        $sql = "SELECT * FROM `$table` WHERE $whereString;";
        $statement = $this->createPreparedStatement($sql);
        $statement->execute();

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        $rowsWithFilteredFields = null;
        if ($fields) {
            foreach ($rows as $row) {
                $rowsWithFilteredFields[] = array_intersect_key($row, array_flip($fields));
            }
        }

        return $rowsWithFilteredFields ?? $rows;
    }

    /**
     * Returns the record with the highest id of the given table.
     *
     * @param string $table Table name
     *
     * @return array last inserted row
     */
    protected function findLastInsertedTableRow(string $table): array
    {
        $sql = sprintf('SELECT * FROM `%s` ORDER BY id DESC LIMIT 1', $table);
        $statement = $this->createPreparedStatement($sql);
        $statement->execute();

        return $statement->fetch(PDO::FETCH_ASSOC) ?? [];
    }

    /**
     * Asserts that results of given table are the same as the given row.
     *
     * @param array $expectedRow Row expected to find
     * @param string $table Table to look into
     * @param string $whereColumn The column of the search query
     * @param mixed $whereValue The value that will be searched for
     * @param array|null $fields The array of fields
     * @param string $message Optional message
     *
     * @return void
     */
    protected function assertTableRowsByColumn(
        array $expectedRow,
        string $table,
        string $whereColumn,
        mixed $whereValue,
        array $fields = null,
        string $message = ''
    ): void {
        $rows = $this->findTableRowsByColumn($table, $whereColumn, $whereValue, $fields ?: array_keys($expectedRow));
        foreach ($rows as $row) {
            $this->assertSame(
                $expectedRow,
                $row,
                $message
            );
        }
    }
}
