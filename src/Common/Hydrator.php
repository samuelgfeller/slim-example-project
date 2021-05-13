<?php


namespace App\Common;


final class Hydrator
{
    /**
     * Hydrate a collection of objects with data from an array with multiple items.
     *
     * @template T
     *
     * @param array $rows The items
     * @param class-string<T> $class The FQN
     *
     * @return T[] The list of object
     */
    public function hydrate(array $rows, string $class): array
    {
        /** @var T[] $result */
        $result = [];

        foreach ($rows as $row) {
            $result[] = new $class($row);
        }

        return $result;
    }

    /**
     * Hydrate a collection of objects that are aggregates, meaning they contain loaded associate objects
     *
     * @template T
     *
     * @param array<array> $aggregateRows Rows with values from different tables
     * @param array<class-string<T>|string> $aggregateClass main class that will have instances of given
     * associations (first array value) and alias used in query (second)
     * @param array<array<class-string|string>> $associations classes (first), beginning of alias used in query (second)
     * and its attribute name in the main class (third)
     *
     * @return T[] The list of object
     */
    public function hydrateAggregates(array $aggregateRows, array $aggregateClass, array $associations) {
        $aggregateObjects = [];
        // Alias name is second value of array
        $mainAlias = $aggregateClass[1];
        foreach ($aggregateRows as $rowValues) {
            /** @var array<array<mixed>> $moduleRows reconstruction of result rows but separated by table */
            $moduleRows = [];
            foreach ($rowValues as $column => $value) {
                // Check if column name (alias) starts with given alias (e.g. "post_")
                if (str_starts_with($column, $mainAlias)) {
                    // Add value to $moduleRows sub array for the reconstruction of clean rows bound to table
                    $moduleRows[$mainAlias][str_replace($mainAlias, '', $column)] = $value;
                }
                // Put all aggregate values in distinct array
                foreach ($associations as $association) {
                    $associateAlias = $association[1];
                    // Check if column name (alias) starts with given alias (e.g. "post_")
                    if (str_starts_with($column, $associateAlias)) {
                        // Add value to $moduleRows sub array for the reconstruction of clean rows bound to table
                        $moduleRows[$associateAlias][str_replace($associateAlias, '', $column)] = $value;
                    }
                }
            }
            // Class name is the first value of the $aggregateClass array
            /** @var T $aggregateObj */
            $aggregateObj = new $aggregateClass[0]($moduleRows[$mainAlias] ?? []);
            // Add associations to main object (aggregate)
            foreach ($associations as $association) {
                // Attribute name of association in main object is third value
                $attr = $association[2]; // Array to string exception if not done via variable
                // Associate class name is first value of $association array and alias is the second
                $aggregateObj->$attr = new $association[0]($moduleRows[$association[1]] ?? []);
                // Cleartext example: $post->user = new User(['first_name' => 'Bill', 'second_name' => 'Gates']);
            }

            $aggregateObjects[] = $aggregateObj;
        }
        return $aggregateObjects;
    }
}