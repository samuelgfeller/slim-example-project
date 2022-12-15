<?php

namespace App\Domain\Client\Service;

use App\Infrastructure\Factory\QueryFactory;

class ClientFilterWhereConditionBuilder
{
    private string $columnPrefix = 'client.';

    public function __construct(
        private readonly QueryFactory $queryFactory,
    ) {
    }

// In the select query there are multiple joins. This is the alias for the client table.

    /**
     * Build cakephp query builder where array with filter params.
     *
     * @param array $filterParams default deleted_at null
     *
     * @return array
     */
    public function buildWhereArrayWithFilterParams(array $filterParams = ['deleted_at' => null]): array
    {
        // Build where array for cakephp query builder
        $queryBuilderWhereArray = [];
        // Name is a special case in the filtering
        if (isset($filterParams['name'])) {
            $namePartsConditions = [];
            if (str_contains($filterParams['name'], ' ')) {
                $nameParts = explode(' ', $filterParams['name'], 2);
                // Check if name part are from first name or last name
                $namePartsConditions['OR'] = [
                    [
                        [$this->columnPrefix . 'first_name LIKE' => '%' . $nameParts[0] . '%'],
                        [$this->columnPrefix . 'last_name LIKE' => '%' . $nameParts[1] . '%'],
                    ],
                    [
                        [$this->columnPrefix . 'first_name LIKE' => '%' . $nameParts[1] . '%'],
                        [$this->columnPrefix . 'last_name LIKE' => '%' . $nameParts[0] . '%'],
                    ],
                ];
            }
            $query = $this->queryFactory->newQuery();
            // Name is always an AND condition
            $firstAndLastNameConcat = $query->newExpr()->like(
                $query->func()->concat(
                    [
                        $query->identifier($this->columnPrefix . 'first_name'),
                        ' ',
                        $query->identifier($this->columnPrefix . 'last_name'),
                    ],
                    ['string', 'string', 'string']
                ),
                '%' . $filterParams['name'] . '%',
                'string'
            );
            $queryBuilderWhereArray[]['OR'] = [
                [$this->columnPrefix . 'first_name LIKE' => '%' . $filterParams['name'] . '%'],
                [$this->columnPrefix . 'last_name LIKE' => '%' . $filterParams['name'] . '%'],
                $firstAndLastNameConcat,
                $namePartsConditions,
            ];
            unset($filterParams['name']);
        }

        foreach ($filterParams as $column => $value) {
            // If multiple values are given for a filter setting, separate by OR
            if (is_array($value)) {
                $orConditions = [];
                foreach ($value as $rowId) {
                    $value = $rowId;
                    // Create column clone otherwise column (which is the same for each iteration of this loop) would
                    // have "client." prepended in each iteration
                    $columnClone = $column;
                    $this->adaptColumnValueToQueryBuilder($columnClone, $value);
                    $orConditions[][$columnClone] = $value;
                }
                // Add OR with conditions to where array
                $queryBuilderWhereArray[]['OR'] = $orConditions;
            } else {
                $this->adaptColumnValueToQueryBuilder($column, $value);
                $queryBuilderWhereArray[$column] = $value;
            }
        }

        return $queryBuilderWhereArray;
    }

    /**
     * Change column and value to valid cakephp query builder
     * conditions for the client list request.
     *
     * @param string $column column reference
     * @param string|int|array|null $value value reference
     */
    private function adaptColumnValueToQueryBuilder(string &$column, null|string|int|array &$value): void
    {
        // If empty string it means that value should be null
        if ($value === '') {
            $value = null;
        }
        // If expected value is "null" the word "IS" is needed in the array key right after the column
        $is = '';
        // If " IS" is already in column, it doesn't have to be added
        if ($value === null && !str_contains($column, ' IS')) {
            $is = ' IS'; // To be added right after column
        }
        $column = "$this->columnPrefix$column$is";
    }
}
