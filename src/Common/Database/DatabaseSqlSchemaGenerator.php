<?php

namespace App\Common\Database;

use PDO;
use PDOStatement;
use UnexpectedValueException;

/**
 * Generates schema.sql with current database.
 */
class DatabaseSqlSchemaGenerator
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly string $rootPath
    ) {
    }

    /**
     * Generates schema.sql from current database
     * Source: https://github.com/odan/slim4-skeleton/commit/c9450a3ea1e18417f13cb27597ee922ab71c8592.
     *
     * @return int
     */
    public function generateSqlSchema(): int
    {
        // Lazy loading, because the database may not exist
        echo sprintf('Use database: %s', (string)$this->query('select database()')->fetchColumn());

        $statement = $this->query(
            'SELECT table_name
                        FROM information_schema.tables
                        WHERE table_schema = database()'
        );

        $sql = [];
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $row = array_change_key_case($row);
            $statement2 = $this->query(sprintf('SHOW CREATE TABLE `%s`;', (string)$row['table_name']));
            $createTableSql = $statement2->fetch()['Create Table'];
            $sql[] = preg_replace('/AUTO_INCREMENT=\d+/', '', $createTableSql) . ';';
        }

        $sql = implode("\n\n", $sql);

        $filename = $this->rootPath . '/resources/schema/schema.sql';
        file_put_contents($filename, $sql);

        echo sprintf("\nGenerated file: %s", realpath($filename));

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
            throw new UnexpectedValueException('Query failed');
        }

        return $statement;
    }
}
