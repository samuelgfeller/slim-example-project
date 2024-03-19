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
    public static function clientListFilterCases(): array
    {
        // ID does not matter as long as it's not used as default value by the fixtures
        $newcomerAttributes = ['id' => 42, 'user_role_id' => UserRole::NEWCOMER];
        $sqlDateTime = (new \DateTime())->format('Y-m-d H:i:s');
        // Instead of inserting only the clients we need each time, all relevant clients are inserted to also test that
        // clients that are not supposed to be found are not returned even if they exist in the database
        // To test for multiple results, at least 2 clients are inserted for each filter
        $clientsToInsert = [
            // Unassigned
            ['id' => 1, 'user_id' => null, 'first_name' => 'First'],
            ['id' => 2, 'user_id' => null, 'first_name' => 'Second'],
            // Assigned to me (user id hardcoded - they must be provided in 'usersToInsert' with this id as attribute)
            ['id' => 3, 'user_id' => 42, 'first_name' => 'Third'],
            ['id' => 4, 'user_id' => 42, 'client_status_id' => 68, 'first_name' => 'Fourth'],
            // Deleted
            ['id' => 5, 'deleted_at' => $sqlDateTime, 'first_name' => 'Fifth'],
            // Deleted, linked to other user and status 68
            ['id' => 7, 'deleted_at' => $sqlDateTime, 'user_id' => 43, 'client_status_id' => 68, 'first_name' => 'Seventh'],
            // Assigned to other user than the authenticated one
            ['id' => 8, 'user_id' => 43, 'first_name' => 'Eighth'],
            ['id' => 9, 'user_id' => 43, 'first_name' => 'Ninth'],
            // Assigned to status
            ['id' => 10, 'client_status_id' => 68, 'first_name' => 'Tenth'],
            ['id' => 12, 'client_status_id' => 69, 'first_name' => 'Twelfth'], // Assigned to other status
            // Assigned to status 68, user 42 and deleted
            ['id' => 11, 'client_status_id' => 68, 'deleted_at' => $sqlDateTime, 'user_id' => 42, 'first_name' => 'Eleventh'],
            // Assigned to deleted user
            ['id' => 13, 'user_id' => 44, 'first_name' => 'Assigned to deleted user'],
        ];
        // ! Users to insert attributes. Has to at least contain all 'user_id' from the clients to insert array
        $usersToInsert = [
            // User id 1 is fixture default, so it has to be inserted or always be in attributes
            ['id' => 1],
            ['id' => 43],
            ['id' => 44, 'deleted_at' => $sqlDateTime],
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
                'filterQueryParamsArr' => ['user' => ''], // Query params for "unassigned"
                'expectedClientsWhereString' => 'client.deleted_at IS NULL AND user_id IS NULL',
                'authenticatedUserAttributes' => $newcomerAttributes,
                'clientsToInsert' => $clientsToInsert,
                'usersToInsert' => $usersToInsert,
                'clientStatusesToInsert' => $clientStatusesToInsert,
            ],
            [ // Test user filter "assigned to user" could be other user or authenticated user
                'filterQueryParamsArr' => ['user' => 42],
                'expectedClientsWhereString' => 'client.deleted_at IS NULL AND user_id = 42',
                'authenticatedUserAttributes' => $newcomerAttributes,
                'clientsToInsert' => $clientsToInsert,
                'usersToInsert' => $usersToInsert,
                'clientStatusesToInsert' => $clientStatusesToInsert,
            ],
            [ // Test user filter "unassigned" and "assigned to me" together
                'filterQueryParamsArr' => ['user' => ['', 1]], // Query params for "unassigned"
                'expectedClientsWhereString' => 'client.deleted_at IS NULL AND (user_id IS NULL OR user_id = 1)',
                'authenticatedUserAttributes' => $newcomerAttributes,
                'clientsToInsert' => $clientsToInsert,
                'usersToInsert' => $usersToInsert,
                'clientStatusesToInsert' => $clientStatusesToInsert,
            ],
            // * Filter "deleted-assigned-user"
            [ // Test user filter "deleted-assigned-user"
                'filterQueryParamsArr' => ['deleted-assigned-user' => 1], // Query params for this filter
                'expectedClientsWhereString' => 'client.deleted_at IS NULL AND user.deleted_at IS NOT NULL',
                // Managing advisor or advisor needed for the deleted filter.
                'authenticatedUserAttributes' => ['id' => 42, 'user_role_id' => UserRole::MANAGING_ADVISOR],
                'clientsToInsert' => $clientsToInsert,
                'usersToInsert' => $usersToInsert,
                'clientStatusesToInsert' => $clientStatusesToInsert,
            ],
            // * Test filter "deleted"
            [ // Test filter deleted
                'filterQueryParamsArr' => ['deleted' => 1], // Query params for "deleted"
                'expectedClientsWhereString' => 'client.deleted_at IS NOT NULL',
                // Managing advisor needed for the deleted filter.
                'authenticatedUserAttributes' => ['id' => 42, 'user_role_id' => UserRole::MANAGING_ADVISOR],
                'clientsToInsert' => $clientsToInsert,
                'usersToInsert' => $usersToInsert,
                'clientStatusesToInsert' => $clientStatusesToInsert,
            ],
            // * Test filter "status"
            [ // Test user filter "assigned to other user"
                'filterQueryParamsArr' => ['status' => 68], // Status id that is not fixture default
                'expectedClientsWhereString' => 'client.deleted_at IS NULL AND (client_status_id = 68)',
                'authenticatedUserAttributes' => $newcomerAttributes,
                'clientsToInsert' => $clientsToInsert,
                'usersToInsert' => $usersToInsert,
                'clientStatusesToInsert' => $clientStatusesToInsert,
            ],
            // * Test search filter "name"
            [ // Test user filter "name"
                'filterQueryParamsArr' => ['name' => 'th'],
                'expectedClientsWhereString' => 'client.deleted_at IS NULL AND (CONCAT(client.first_name, "  ", ' .
                    'last_name) LIKE "%th%")',
                'authenticatedUserAttributes' => $newcomerAttributes,
                'clientsToInsert' => $clientsToInsert,
                'usersToInsert' => $usersToInsert,
                'clientStatusesToInsert' => $clientStatusesToInsert,
            ],
            // * Filter combination "status" - "name"
            [ // Test user filter "status 68 or 69" and "name contains 'enth'"
                'filterQueryParamsArr' => ['status' => [68, 69], 'name' => 'enth'],
                'expectedClientsWhereString' => 'client.deleted_at IS NULL AND (client_status_id IN (68, 69)) AND ' .
                    '(CONCAT(client.first_name, "  ", last_name) LIKE "%enth%")',
                'authenticatedUserAttributes' => $newcomerAttributes,
                'clientsToInsert' => $clientsToInsert,
                'usersToInsert' => $usersToInsert,
                'clientStatusesToInsert' => $clientStatusesToInsert,
            ],
            // * Filter combination "user" - "status"
            [ // Test user filter "status 68 or 69" and "user 1 (default when not in attr) or 42 (authenticated)"
                'filterQueryParamsArr' => ['status' => [68, 69], 'user' => [1, 42]],
                'expectedClientsWhereString' => 'client.deleted_at IS NULL AND (client_status_id IN (68, 69) AND user_id IN (1, 42))',
                'authenticatedUserAttributes' => $newcomerAttributes,
                'clientsToInsert' => $clientsToInsert,
                'usersToInsert' => $usersToInsert,
                'clientStatusesToInsert' => $clientStatusesToInsert,
            ],
            // * Filter combination: linked to other user 43 or authenticated user 42, is deleted and status is 68
            [ // This test is as newcomer so no clients should be found
                'filterQueryParamsArr' => ['status' => [68], 'user' => [42, 43], 'deleted' => 1],
                'expectedClientsWhereString' => 'FALSE', // ? Newcomer may not see deleted clients
                'authenticatedUserAttributes' => $newcomerAttributes,
                'clientsToInsert' => $clientsToInsert,
                'usersToInsert' => $usersToInsert,
                'clientStatusesToInsert' => $clientStatusesToInsert,
            ],
            [ // Same filter params but this time as managing advisor
                'filterQueryParamsArr' => ['status' => [68], 'user' => [42, 43], 'deleted' => 1],
                'expectedClientsWhereString' => 'client.deleted_at IS NOT NULL AND (client_status_id = 68 AND user_id IN (42, 43))',
                'authenticatedUserAttributes' => ['id' => 42, 'user_role_id' => UserRole::MANAGING_ADVISOR],
                'clientsToInsert' => $clientsToInsert,
                'usersToInsert' => $usersToInsert,
                'clientStatusesToInsert' => $clientStatusesToInsert,
            ],
        ];
    }

    /**
     * Returns invalid filters.
     *
     * @return array
     */
    public static function clientListInvalidFilterCases(): array
    {
        return [
            // Invalid "user" filter
            [
                'filterQueryParamsArr' => ['user' => 'invalid_value'],
                // Provide letters instead of empty string, numeric or array
                'expectedBody' => [
                    'status' => 'error',
                    'message' => 'Invalid filter format "user".',
                ],
            ],
            [
                'filterQueryParamsArr' => ['status' => 'invalid_value'],
                // Provide letters instead of empty string, numeric or array
                'expectedBody' => [
                    'status' => 'error',
                    'message' => 'Invalid filter format "status".',
                ],
            ],
        ];
    }
}
