<?php

namespace App\Test\Traits;

use App\Test\Fixture\FixtureInterface;
use Selective\TestTrait\Traits\DatabaseTestTrait;

/**
 * Util trait to better deal with fixtures in tests.
 */
trait FixtureTestTrait
{
    use DatabaseTestTrait;

    /**
     * Inserts fixtures with given attributes and returns rows with id
     * This has to advantage to remove the dependency of each fixtures'
     * records as the relevant values are passed by the given $attributes.
     *
     * @param array $attributes array of db column name and the expected value an array of multiple attribute sets
     * Format: ['field_name' => 'expected_value', 'other_field_name' => 'other expected value',] or
     * [['field_name' => 'expected_value'], ['field_name' => 'expected_value']] -> makes 2 insets
     * @param FixtureInterface $fixture
     * @param int $amount
     *
     * @return array row when $amount is 1 or array of rows when more than 1 inserted
     */
    protected function insertFixturesWithAttributes(
        array $attributes,
        FixtureInterface $fixture,
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
            $records = $this->getFixtureRecordsWithAttributes($attributesForOneRow, $fixture, $amount);
            // Check if $records is a collection of records or only one (if amount > 1 it will be a collection)
            if (!(isset($records[0]) && is_array($records[0]))) {
                $row = $records;
                // If only one record, insert it and return row
                $row['id'] = (int)$this->insertFixture($fixture->getTable(), $row);
                $recordsCollection[] = $row;
                continue;
            }
            // Loop through records and insert them
            foreach ($records as $key => $row) {
                // Insert records and add id to collection
                $records[$key]['id'] = (int)$this->insertFixture($fixture->getTable(), $row);
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
     * Takes the default record and modifies it to suit given attributes.
     * This makes a lot more sense than storing all different kinds
     * of records in each fixture and then searching them as the test
     * function can control fixtures, and there are no dependencies.
     *
     * @param array $attributes
     * @param FixtureInterface $fixture
     * @param int $amount amount rows that will be returned
     *
     * @return array either one row of requested fixture or array of rows if $amount is higher than 1
     */
    private function getFixtureRecordsWithAttributes(
        array $attributes,
        FixtureInterface $fixture,
        int $amount = 1
    ): array {
        $rows = $fixture->getRecords();
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
}
