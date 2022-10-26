<?php

namespace App\Test\Provider\Client;

use App\Test\Fixture\FixtureTrait;
use App\Test\Fixture\UserFixture;
use Fig\Http\Message\StatusCodeInterface;

class ClientCreateCaseProvider
{

    use FixtureTrait;

    /**
     * Provide malformed request body for client creation
     *
     * @return array[]
     */
    public function malformedRequestBody(): array
    {
        return [
            [
                // If any of the list except client_message is missing it's a bad request
                'missing_first_name' => [
                    'last_name' => 'value',
                    'birthdate' => 'value',
                    'location' => 'value',
                    'phone' => 'value',
                    'email' => 'value',
                    'sex' => 'value',
                    'client_message' => 'value',
                    'user_id' => 'value',
                    'client_status_id' => 'value',
                ],
                'key_too_much_without_client_message' => [
                    'first_name' => 'value',
                    'last_name' => 'value',
                    'birthdate' => 'value',
                    'location' => 'value',
                    'phone' => 'value',
                    'email' => 'value',
                    'sex' => 'value',
                    // 'client_message' => 'value',
                    'user_id' => 'value',
                    'client_status_id' => 'value',
                    'key_too_much' => 'value',
                ],
            ]
        ];
    }

    /**
     * Client creation authorization
     * Provides combination of different user roles with expected result.
     * This tests the rules in ClientAuthorizationChecker.
     *
     * @return array[]
     */
    public function provideUsersAndExpectedResultForClientCreation(): array
    {
        // Get users with different roles
        $managingAdvisorAttributes = ['user_role_id' => 2];
        $advisorAttributes = ['user_role_id' => 3];
        $newcomerAttributes = ['user_role_id' => 4];

        $authorizedResult = [
            StatusCodeInterface::class => StatusCodeInterface::STATUS_CREATED,
            'db_entry_created' => true,
            'json_response' => [
                'status' => 'success',
                'data' => null,
            ],
        ];
        $unauthorizedResult = [
            StatusCodeInterface::class => StatusCodeInterface::STATUS_FORBIDDEN,
            'db_entry_created' => false,
            'json_response' => [
                'status' => 'error',
                'message' => 'Not allowed to create a client.',
            ]
        ];
        return [
            // User role and when "owner" is mentioned, it is always from the perspective of the authenticated user
            [ // ? Newcomer owner - not allowed
                'user_linked_to_client' => $newcomerAttributes,
                'authenticated_user' => $newcomerAttributes,
                'expected_result' => $unauthorizedResult
            ],
            [ // ? Advisor owner - allowed
                'user_linked_to_client' => $advisorAttributes,
                'authenticated_user' => $advisorAttributes,
                'expected_result' => $authorizedResult,
            ],
            [ // ? Advisor not owner - not allowed
                'user_linked_to_client' => $newcomerAttributes,
                'authenticated_user' => $advisorAttributes,
                'expected_result' => $unauthorizedResult,
            ],
            [ // ? Managing not owner - allowed
                'user_linked_to_client' => $advisorAttributes,
                'authenticated_user' => $managingAdvisorAttributes,
                'expected_result' => $authorizedResult,
            ],
        ];
    }
}