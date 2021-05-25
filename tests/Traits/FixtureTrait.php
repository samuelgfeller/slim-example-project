<?php


namespace App\Test\Traits;


use App\Test\Fixture\UserFixture;

/**
 * Util. trait to better deal with fixtures in tests
 */
trait FixtureTrait
{
    /**
     * Returns fixture rows where given condition matches
     * Note: this relies on the function of selective/test-traits DatabaseTestTrait.php
     * @param array<string, mixed> $conditions array of db field name and the expected value. Example:
     *  ['field_name' => 'expected_value', 'other_field_name' => 'other expected value',]
     * @param class-string $class
     * @return array records matching the conditions
     */
    protected function findRecordsFromFixtureWhere(array $conditions, string $class): array
    {
        $fixture = new $class();
        $rows = $fixture->records;
        $matchingRecords = [];
        // Loop over all records (rows)
        foreach ($rows as $row) {
            // Check if condition matches on every field
            foreach ($conditions as $conditionField => $conditionValue) {
                // If the current condition (in loop) is about the field, check the value
                if ($row[$conditionField] !== $conditionValue) {
                    // If one value of the row does not match the condition, the rest of the current rows iteration is skipped
                    continue 2;
                }
            }
            // If all conditions matched, this part is not skipped (with continue) and row is added to matching records
            $matchingRecords[] = $row;
        }
        return $matchingRecords;
    }
}