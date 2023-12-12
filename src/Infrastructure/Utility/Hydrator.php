<?php

namespace App\Infrastructure\Utility;

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
        $result = [];

        foreach ($rows as $row) {
            // Some classes like UserData have a restriction
            $result[] = new $class($row, true);
        }

        return $result;
    }
}
