<?php

namespace App\Domain\Client\Service;

use App\Infrastructure\Factory\QueryFactory;
use Cake\Database\Expression\QueryExpression;
use Cake\Database\Query;

class ClientFilterWhereConditionBuilder
{
    private
    string $columnPrefix = 'client.';

    public function __construct(
        private readonly QueryFactory $queryFactory,
    ) {
    }

// In the select query there are multiple joins. This is the alias for the client table.


    /**
     * Build cakephp query builder where array with filter params
     *
     * @param array $filterParams default deleted_at null
     * @return array
     */
    public function buildWhereArrayWithFilterParams(array $filterParams = ['deleted_at' => null]): array
    {
        // Build where array for cakephp query builder
        $queryBuilderWhereArray = [];
        // Name is a special case in the filtering
        if (isset($filterParams['name'])) {
            $query = $this->queryFactory->newQuery();
            // Name is always an AND condition
            $c = $query->func()->concat(['client.first_name' => 'identifier', ' ', 'client.last_name' => 'identifier']);
            $firstAndLastNameConcat = $query->where(
                function (QueryExpression $exp, Query $query) use ($filterParams) {
                    return $exp->like(
                        $query->func()->concat(
                            [$query->identifier('first_name'), ' ', $query->identifier('last_name')],
                            ['string', 'string', 'string']
                        ),
                        '%' . $filterParams['name'] . '%',
                        'string'
                    );
                }
            );
            $queryBuilderWhereArray[]['OR'] = [
                [$this->columnPrefix . 'first_name LIKE' => '%' . $filterParams['name'] . '%'],
                [$this->columnPrefix . 'last_name LIKE' => '%' . $filterParams['name'] . '%'],
                // $firstAndLastNameConcat
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
     * conditions for the client list request
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