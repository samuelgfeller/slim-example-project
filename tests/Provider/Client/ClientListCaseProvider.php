<?php


namespace App\Test\Provider\Client;


class ClientListCaseProvider
{
    /**
     * Returns every filter combination
     *
     * @return array GET params with valid filter values
     */
    public function provideValidClientListFilters(): array
    {
        return [
            // No filter
            [
                // Filter values for url GET query parameters
                'queryParams' => [],
                // Equivalent record filter to filter database records to assert json response
                'rowFilter' => ['deleted_at' => null]
            ],
            // Not existing filter should return all clients by default
            [
                'queryParams' => ['non-existent-filter' => 'value'],
                'rowFilter' => ['deleted_at' => null]
            ],
            // Client linked to user
            [
                // Filter values for url GET query parameters
                'queryParams' => ['user' => 1],
                // Equivalent record filter to filter database records to assert json response
                'rowFilter' => ['user_id' => 1, 'deleted_at' => null]
            ],
            // Clients linked to logged-in user
            [
                'queryParams' => ['user' => 'session'],
                // 'session' gets replaced with authenticated user in test function
                'rowFilter' => ['user_id' => 'session', 'deleted_at' => null]
            ],
            // Combined filter
            [
                'queryParams' => ['user' => 'session', 'include-deleted' => '1'],
                'rowFilter' => ['user_id' => 'session']
            ],

            // Expandable with more filter
        ];
    }

    /**
     * Returns invalid filters
     *
     * @return array GET params with invalid filter values and expected return body
     */
    public function provideInvalidClientListFilter(): array
    {
        return [
            // Invalid 'user' filter
            [
                'queryParams' => ['user' => 'invalid_value'], // Provide letters instead of numeric or 'session'
                'expectedReturnBody' => [
                    'status' => 'error',
                    'message' => 'Invalid filter format "user".',
                ],
            ],
            // Expandable with more filter
        ];
    }
}