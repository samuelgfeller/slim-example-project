<?php

namespace App\Infrastructure\Console;

use PDO;
use PDOStatement;
use UnexpectedValueException;

/**
 * Generates schema.sql with current database.
 */
final readonly class SqlSchemaGenerator
{
    public function __construct(
        private PDO $pdo,
        private string $rootPath
    ) {
    }

    /**
     * Generates schema.sql with the current database.
     * Used by the command line.
     *
     * @return int
     */
    public function generateSqlSchema(): int
    {
        echo sprintf('Use database: %s', (string)$this->query('select database()')->fetchColumn());

        // Execute SQL query to get all table names from the current database
        $statement = $this->query('SELECT table_name FROM information_schema.tables WHERE table_schema = database()');

        $sql = [];
        // Loop through each table in the database
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            // Changes the case of the keys in the fetched row to lower case
            $row = array_change_key_case($row);
            // Execute SQL query to get the 'CREATE TABLE' statement for the current table
            // SHOW CREATE TABLE is specific to MySQL
            $statement2 = $this->query(sprintf('SHOW CREATE TABLE `%s`;', (string)$row['table_name']));
            // Fetch the 'CREATE TABLE' statement and remove the 'AUTO_INCREMENT' part
            $createTableSql = $statement2->fetch()['Create Table'];
            $sql[] = preg_replace('/AUTO_INCREMENT=\d+/', '', $createTableSql) . ';';
        }

        // Join all the 'CREATE TABLE' statements into a single string, separated by two newlines
        $sql = implode("\n\n", $sql);

        // Write the generated SQL statements to a file
        $filename = $this->rootPath . '/resources/schema/schema.sql';
        file_put_contents($filename, $sql);

        echo sprintf("\nGenerated file: %s", realpath($filename));

        // Return 0 to indicate that the method has completed successfully
        return 0;
    }

    /**
     * Create query statement.
     *
     * @param string $sql The sql
     *
     * @throws UnexpectedValueException
     *
     * @return PDOStatement The statement
     */
    private function query(string $sql): PDOStatement
    {
        $statement = $this->pdo->query($sql);

        if (!$statement) {
            throw new UnexpectedValueException(
                'Query failed: ' . $sql . ' Error: ' . $this->pdo->errorInfo()[2]
            );
        }

        return $statement;
    }
}
