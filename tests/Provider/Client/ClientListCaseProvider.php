<?php


namespace App\Test\Provider\Client;


use App\Domain\User\Enum\UserRole;

class ClientListCaseProvider
{
    /**
     * Returns every filter combination
     *
     * @return array GET params with valid filter values
     */
    public function provideValidClientListFilters(): array
    {
        $newcomerAttributes = ['id' => 1, 'user_role_id' => UserRole::NEWCOMER];
        // Instead of inserting only the clients we need each time, all relevant clients are inserted to also test that
        // clients that are not supposed to be found are not returned even if they exist in the database
        // To test single and multiple results, at least 2 clients are inserted for each filter
        $clientsToInsert = [
            // Unassigned
            ['user_id' => null, 'first_name' => 'First'],
            ['user_id' => null, 'first_name' => 'Second'],
            // Assigned to me (user id hardcoded - they must be provided in 'users_to_insert' with this id as attribute)
            ['user_id' => 1, 'first_name' => 'First'],
            ['user_id' => 1, 'first_name' => 'Second'],
            // Deleted
            ['deleted_at' => (new \DateTime())->format('Y-m-d H:i:s'), 'first_name' => 'First'],
            ['deleted_at' => (new \DateTime())->format('Y-m-d H:i:s'), 'first_name' => 'Second'],
            // Unassigned
            ['user_id' => null, 'first_name' => 'First'],
            ['user_id' => null, 'first_name' => 'Second'],
        ];
        // ! Users to insert attributes. Has to at least contain all 'user_id' from the clients to insert array
        $usersToInsert = [['id' => 2],]; // User id 1 is already provided as authenticated user
        // ! Client statuses to insert attributes. Has to at least contain all hardcoded 'client_status_id'
        $clientStatusesToInsert = [['id' => 1],]; // Status id 1 is default for client fixture

        // There are 3 "custom" filters unassigned, assigned_to_me, deleted
        return [
            // Filter "user"
            [ // Test user filter "unassigned"
                'get_params_array' => ['user' => ''], // Query params for "unassigned"
                'expected_clients_where_string' => 'user_id IS NULL',
                'authenticated_user' => $newcomerAttributes,
                'clients_to_insert' => $clientsToInsert,
                'users_to_insert' => $usersToInsert,
                'statuses_to_insert' => $clientStatusesToInsert,
            ],

        ];
        // All client status entries are possible filters
        // All users are possible filters
        $arr = [
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