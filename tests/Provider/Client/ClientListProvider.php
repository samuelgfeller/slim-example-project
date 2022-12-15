<?php

namespace App\Test\Provider\Client;

use App\Domain\User\Enum\UserRole;

class ClientListProvider
{
    /**
     * Returns every filter combination.
     *
     * @return array GET params with valid filter values
     */
    public function clientListFilterCases(): array
    {
        // ID does not matter as long as it's not used as default value by the fixtures
        $newcomerAttributes = ['id' => 42, 'user_role_id' => UserRole::NEWCOMER];
        $sqlDateTime = (new \DateTime())->format('Y-m-d H:i:s');
        // Instead of inserting only the clients we need each time, all relevant clients are inserted to also test that
        // clients that are not supposed to be found are not returned even if they exist in the database
        // To test for multiple results, at least 2 clients are inserted for each filter
        $clientsToInsert = [
            // Unassigned
            ['user_id' => null, 'first_name' => 'First'],
            ['user_id' => null, 'first_name' => 'Second'],
            // Assigned to me (user id hardcoded - they must be provided in 'users_to_insert' with this id as attribute)
            ['user_id' => 42, 'first_name' => 'Third'],
            ['user_id' => 42, 'client_status_id' => 68, 'first_name' => 'Fourth'],
            // Deleted
            ['deleted_at' => $sqlDateTime, 'first_name' => 'Fifth'],
            // Deleted, linked to other user and status 68
            ['deleted_at' => $sqlDateTime, 'user_id' => 43, 'client_status_id' => 68, 'first_name' => 'Seventh'],
            // Assigned to other user
            ['user_id' => 43, 'first_name' => 'Eighth'],
            ['user_id' => 43, 'first_name' => 'Ninth'],
            // Assigned to status
            ['client_status_id' => 68, 'first_name' => 'Tenth'],
            ['client_status_id' => 69, 'first_name' => 'Twelfth'], // Assigned to other status
            // Assigned to status 68, user 42 and deleted
            ['client_status_id' => 68, 'deleted_at' => $sqlDateTime, 'user_id' => 42, 'first_name' => 'Eleventh'],
        ];
        // ! Users to insert attributes. Has to at least contain all 'user_id' from the clients to insert array
        $usersToInsert = [
            // User id 1 is fixture default, so it has to be inserted or always be in attributes
            ['id' => 1],
            ['id' => 43],
        ];
        // ! Client statuses to insert attributes. Has to at least contain all hardcoded 'client_status_id'
        $clientStatusesToInsert = [
            // Status id 1 is default for client fixture, so it has to be inserted (or be in each attribute)
            ['id' => 1],
            ['id' => 68],
            ['id' => 69],
        ];

        // There are 3 "custom" filters unassigned, assigned_to_me, deleted
        return [
            // * Filter "user"
            [ // Test user filter "unassigned"
                'get_params' => ['user' => ''], // Query params for "unassigned"
                'expected_clients_where_string' => 'deleted_at IS NULL AND user_id IS NULL',
                'authenticated_user' => $newcomerAttributes,
                'clients_to_insert' => $clientsToInsert,
                'users_to_insert' => $usersToInsert,
                'statuses_to_insert' => $clientStatusesToInsert,
            ],
            [ // Test user filter "assigned to user" could be other user or authenticated user
                'get_params' => ['user' => 42],
                'expected_clients_where_string' => 'deleted_at IS NULL AND user_id = 42',
                'authenticated_user' => $newcomerAttributes,
                'clients_to_insert' => $clientsToInsert,
                'users_to_insert' => $usersToInsert,
                'statuses_to_insert' => $clientStatusesToInsert,
            ],
            [ // Test user filter "unassigned" and "assigned to me" together
                'get_params' => ['user' => ['', 1]], // Query params for "unassigned"
                'expected_clients_where_string' => 'deleted_at IS NULL AND (user_id IS NULL OR user_id = 1)',
                'authenticated_user' => $newcomerAttributes,
                'clients_to_insert' => $clientsToInsert,
                'users_to_insert' => $usersToInsert,
                'statuses_to_insert' => $clientStatusesToInsert,
            ],
            // * Test filter "deleted"
            [ // Test user filter "unassigned" and "assigned to me" together
                'get_params' => ['deleted' => 1], // Query params for "unassigned"
                'expected_clients_where_string' => 'deleted_at IS NOT NULL',
                // Managing advisor needed for the deleted filter.
                'authenticated_user' => ['id' => 42, 'user_role_id' => UserRole::MANAGING_ADVISOR],
                'clients_to_insert' => $clientsToInsert,
                'users_to_insert' => $usersToInsert,
                'statuses_to_insert' => $clientStatusesToInsert,
            ],
            // * Test filter "status"
            [ // Test user filter "assigned to other user"
                'get_params' => ['status' => 68], // Status id that is not fixture default
                'expected_clients_where_string' => 'deleted_at IS NULL AND (client_status_id = 68)',
                'authenticated_user' => $newcomerAttributes,
                'clients_to_insert' => $clientsToInsert,
                'users_to_insert' => $usersToInsert,
                'statuses_to_insert' => $clientStatusesToInsert,
            ],
            // * Test search filter "name"
            [ // Test user filter "name"
                'get_params' => ['name' => 'th'],
                'expected_clients_where_string' => 'deleted_at IS NULL AND (CONCAT(first_name, "  ", ' .
                    'last_name) LIKE "%th%")',
                'authenticated_user' => $newcomerAttributes,
                'clients_to_insert' => $clientsToInsert,
                'users_to_insert' => $usersToInsert,
                'statuses_to_insert' => $clientStatusesToInsert,
            ],
            // * Filter combination "status" - "name"
            [ // Test user filter "status 68 or 69" and "name contains 'enth'"
                'get_params' => ['status' => [68, 69], 'name' => 'enth'],
                'expected_clients_where_string' => 'deleted_at IS NULL AND (client_status_id IN (68, 69)) AND ' .
                    '(CONCAT(first_name, "  ", last_name) LIKE "%enth%")',
                'authenticated_user' => $newcomerAttributes,
                'clients_to_insert' => $clientsToInsert,
                'users_to_insert' => $usersToInsert,
                'statuses_to_insert' => $clientStatusesToInsert,
            ],
            // * Filter combination "user" - "status"
            [ // Test user filter "status 68 or 69" and "user 1 (default when not in attr) or 42 (authenticated)"
                'get_params' => ['status' => [68, 69], 'user' => [1, 42]],
                'expected_clients_where_string' => 'deleted_at IS NULL AND (client_status_id IN (68, 69) AND user_id IN (1, 42))',
                'authenticated_user' => $newcomerAttributes,
                'clients_to_insert' => $clientsToInsert,
                'users_to_insert' => $usersToInsert,
                'statuses_to_insert' => $clientStatusesToInsert,
            ],
            // * Filter combination: linked to other user 43 or authenticated user 42, is deleted and status is 68
            [ // This test is as newcomer so no clients should be found
                'get_params' => ['status' => [68], 'user' => [42, 43], 'deleted' => 1],
                'expected_clients_where_string' => 'FALSE', // ? Newcomer may not see deleted clients
                'authenticated_user' => $newcomerAttributes,
                'clients_to_insert' => $clientsToInsert,
                'users_to_insert' => $usersToInsert,
                'statuses_to_insert' => $clientStatusesToInsert,
            ],
            [ // Same filter params but this time as managing advisor
                'get_params' => ['status' => [68], 'user' => [42, 43], 'deleted' => 1],
                'expected_clients_where_string' => 'deleted_at IS NOT NULL AND (client_status_id = 68 AND user_id IN (42, 43))',
                'authenticated_user' => ['id' => 42, 'user_role_id' => UserRole::MANAGING_ADVISOR],
                'clients_to_insert' => $clientsToInsert,
                'users_to_insert' => $usersToInsert,
                'statuses_to_insert' => $clientStatusesToInsert,
            ],
        ];
    }

    /**
     * Returns invalid filters.
     *
     * @return array
     */
    public function clientListInvalidFilterCases(): array
    {
        return [
            // Invalid "user" filter
            [
                'get_params' => ['user' => 'invalid_value'], // Provide letters instead of empty string, numeric or array
                'response_body' => [
                    'status' => 'error',
                    'message' => 'Invalid filter format "user".',
                ],
            ],
            [
                'get_params' => ['status' => 'invalid_value'], // Provide letters instead of empty string, numeric or array
                'response_body' => [
                    'status' => 'error',
                    'message' => 'Invalid filter format "status".',
                ],
            ],
        ];
    }
}
