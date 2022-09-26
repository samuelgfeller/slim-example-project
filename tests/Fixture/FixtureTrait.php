<?php


namespace App\Test\Fixture;


/**
 * Util. trait to better deal with fixtures in tests
 */
trait FixtureTrait
{
    /**
     * Returns fixture rows where given condition matches
     * Note: this relies on the function of selective/test-traits DatabaseTestTrait.php
     *
     * @param array<string, mixed> $conditions array of db field name and the expected value. Example:
     *  ['field_name' => 'expected_value', 'other_field_name' => 'other expected value',]
     * @param class-string $fixtureClass
     * @return array records matching the conditions
     */
    protected function findRecordsFromFixtureWhere(array $conditions, string $fixtureClass): array
    {
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
            // If all conditions matched, this part is not skipped (with continue) and row is added to matching records
            $matchingRecords[] = $row;
        }
        return $matchingRecords;
    }

    /**
     * If only specific fixtures should be inserted for instance
     * linked to a specific ressource
     *
     * @param array<string, mixed> $conditions array of db column name and the expected value.
     * Shape: ['field_name' => 'expected_value', 'other_field_name' => 'other expected value',]
     * @param class-string $fixtureClass
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