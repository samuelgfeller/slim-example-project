<?php
namespace App\Infrastructure\Persistence;

use Cake\Database\Connection;
use PDO;

abstract class DataManagerOld {

    protected $conn;

    public function __construct(Connection $conn) {
        $this->conn = $conn;
    }

	/**
	 * Insert data in database
	 *
	 * @param string $table
	 * @param array $data assoc array. Key has to be the table name and value its value
	 * @return bool|string
	 */
	public function insert($table, $data) {
		if ($this->conn) {
			$query = 'INSERT INTO ' . $table . ' (' . implode(', ', array_keys($data)) . ')
        VALUES (:' . implode(', :', array_keys($data)) . ');';
			$stmt = $this->conn->prepare($query);
			$stmt->execute($data);
			return $this->conn->lastInsertId();
		}
		return false;
	}

	/**
	 * Run a query for example update or delete
	 *
	 * @param string $query
	 * @param array $args
	 * @return PDO|bool
	 */
	public function run(string $query, array $args = []) {
		if ($conn = $this->pdo) {
			$stmt = $conn->prepare($query);
			$stmt->execute($args);
			return $conn;
		}
		return false;
	}

	/**
	 * Select multiple data from database and return them as an associative array
	 *
	 * @param string $query
	 * @param array $args arguments (? in query)
	 * @return array|bool
	 */
	public function selectAndFetchAssocMultipleData(string $query, array $args = []) {
		if ($conn = $this->pdo) {
			$stmt = $conn->prepare($query);
			$stmt->execute($args);
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}
		return false;
	}

	/**
	 * Select single data from database and return it as an assoc array which is the value
	 *
	 * @param string $query
	 * @param array $args
	 * @return bool|mixed
	 */
	public function selectAndFetchSingleData(string $query, array $args = []) {
		if ($conn = $this->pdo) {
			$stmt = $conn->prepare($query);
			$stmt->execute($args);
			return $stmt->fetch();
		}
		return false;
	}

}
