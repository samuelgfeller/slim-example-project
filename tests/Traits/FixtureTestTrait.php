<?php

namespace App\Test\Traits;

use Selective\TestTrait\Traits\DatabaseTestTrait;

/**
 * Util. trait to better deal with fixtures in tests.
 */
trait FixtureTestTrait
{
    use DatabaseTestTrait;

    /**
     * Takes default record and modifies it to suit given attributes.
     * This makes a lot more sense than storing all different kinds
     * of records in each fixture and then searching them as the test
     * function can control fixtures and there is no dependencies.
     *
     * @param array $attributes
     * @param string $fixtureClass
     * @param int $amount amount rows that will be returned
     *
     * @return array either one row of requested fixture or array of rows if $amount is higher than 1
     */
    protected function getFixtureRecordsWithAttributes(array $attributes, string $fixtureClass, int $amount = 1): array
    {
        $fixture = new $fixtureClass();
        $rows = $fixture->records;
        $returnArray = [];
        $rowKey = 0;
        // To have a pool of different data; instead of taking one basic record when multiple records are asked,
        // this iterates over the existing records
        for ($i = 0; $i <= $amount; $i++) {
            // If there are no more rows for the row key, reset it to 0
            if (!isset($rows[$rowKey])) {
                $rowKey = 0;
            }
            // Remove id from row key before setting new values as id might be a given attribute
            unset($rows[$rowKey]['id']);
            // Add given attributes to row
            foreach ($attributes as $colum => $value) {
                // Set value to given attribute value
                $rows[$rowKey][$colum] = $value;
            }
            $returnArray[] = $rows[$rowKey];
            $rowKey++;
        }

        if ($amount === 1) {
            return $returnArray[0];
        }

        return $returnArray;
    }

    /**
     * Inserts fixtures with given attributes and returns rows with id
     * This has to advantage to remove the dependency of each fixtures'
     * records as the relevant values are passed by the given $attributes.
     *
     * @param array $attributes array of db column name and the expected value an array of multiple attribute sets
     * Format: ['field_name' => 'expected_value', 'other_field_name' => 'other expected value',] or
     * [['field_name' => 'expected_value'], ['field_name' => 'expected_value']] -> makes 2 insets
     * @param class-string $fixtureClass
     * @param int $amount
     *
     * @return array row when $amount is 1 or array of rows when more than 1 inserted
     */
    protected function insertFixturesWithAttributes(
        array $attributes,
        string $fixtureClass,
        int $amount = 1
    ): array {
        $attributesIsMultidimensional = true; // I know there are technically no multidimensional arrays but its more readable
        // Check if attributes is an array of different sets of attributes or directly the attributes
        if (count($attributes) === count($attributes, COUNT_RECURSIVE)) {
            // Put $attributes in an additional array
            $attributes = [$attributes];
            $attributesIsMultidimensional = false;
        }

        $recordsCollection = [];
        foreach ($attributes as $attributesForOneRow) {
            $records = $this->getFixtureRecordsWithAttributes($attributesForOneRow, $fixtureClass, $amount);
            // Check if $records is a collection of records or only one (if amount > 1 it will be a collection)
            if (!(isset($records[0]) && is_array($records[0]))) {
                $row = $records;
                // If only one record, insert it and return row
                $row['id'] = (int)$this->insertFixture((new $fixtureClass())->table, $row);
                $recordsCollection[] = $row;
                continue;
            }
            // Loop through records and insert them
            foreach ($records as $key => $row) {
                // Insert records and add id to collection
                $records[$key]['id'] = (int)$this->insertFixture((new $fixtureClass())->table, $row);
            }
            // If $amount is greater than 1, the results are in a sub-array
            $recordsCollection[] = $records;
        }
        // Checking if attributes is multidimensional and not $recordsCollection to prevent unexpected return value
        // of a single row when a multidimensional array of 1 set of attributes is provided
        if ($attributesIsMultidimensional === false) {
            return $recordsCollection[0];
        }

        return $recordsCollection;
    }

    /**
     * Insert multiple given fixture rows.
     *
     * @param string $table
     * @param array $rows
     *
     * @return array rows with id
     */
    protected function insertFixtureRows(string $table, array $rows): array
    {
        foreach ($rows as $key => $row) {
            $rows[$key]['id'] = (int)$this->insertFixture($table, $row);
        }

        return $rows;
    }

    /**
     * Returns fixture rows where given condition matches
     * Note: this relies on the function of selective/test-traits DatabaseTestTrait.php.
     *
     * @param array<string, mixed> $conditions array of db field name and the expected value. Example:
     *  ['field_name' => 'expected_value', 'other_field_name' => 'other expected value',]
     * @param class-string $fixtureClass
     * @param array $oppositeConditions optional NOT conditions. If ['id' => 1] is provided -> user 1 will NOT be returned
     *
     * @return array[] records matching the conditions
     */
    protected function findRecordsFromFixtureWhere(
        array $conditions,
        string $fixtureClass,
        array $oppositeConditions = []
    ): array {
        $fixture = new $fixtureClass();
        $rows = $fixture->records;
        $matchingRecords = [];
        // Loop over all records (rows)
        foreach ($rows as $row) {
            // Check if condition matches on row columns
            foreach ($conditions as $columnToCheck => $expectedColumnValue) {
                // If the current condition (in loop) is about the field, check the value
                if ($row[$columnToCheck] !== $expectedColumnValue) {
                    // If one value of the row does not match the condition, the rest of the current rows iteration is skipped
                    continue 2;
                }
            }
            // Check if opposite condition matches rows that should NOT be returned
            foreach ($oppositeConditions as $columnToCheck => $expectedColumnValue) {
                // If the current opposite condition (in loop) concerns the $columnToCheck, check if the value matches
                if ($row[$columnToCheck] === $expectedColumnValue) {
                    // If one value of the row matches the condition, the rest of the current rows iteration is skipped
                    continue 2;
                }
            }

            // If all conditions matched, this part is not skipped (with continue) and row is added to matching records
            $matchingRecords[] = $row;
        }

        return $matchingRecords;
    }

    /**
     * If only specific fixtures should be inserted for instance
     * linked to a specific resource.
     *
     * @param array<string, mixed> $conditions array of db column name and the expected value.
     * Shape: ['field_name' => 'expected_value', 'other_field_name' => 'other expected value',]
     * @param class-string $fixtureClass
     *
     * @return void
     */
    protected function insertFixtureWhere(array $conditions, string $fixtureClass): void
    {
        $filteredRecords = $this->findRecordsFromFixtureWhere($conditions, $fixtureClass);
        foreach ($filteredRecords as $row) {
            $this->insertFixture((new $fixtureClass())->table, $row);
        }
    }

    /**
     * Get all rows with the given string as key.
     * This is practical for dynamic targeting for assertion so that it's
     * not necessary to change the code everywhere if I change value for
     * in the rows as the expected value will be targeted via the key.
     *
     * @param string $key that will be the value
     * @param class-string $class
     *
     * @return array
     */
//    public function getFixtureRowsWithValueKey(string $key, string $class): array
//    {
//        $fixture = new $class();
//        $resultRows = [];
//        foreach ($fixture->records as $record) {
//            $resultRows[$record[$key]] = $record;
//        }
//        return $resultRows;
//    }
}
