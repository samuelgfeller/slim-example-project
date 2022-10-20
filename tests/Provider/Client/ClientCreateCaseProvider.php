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
        // Insert user role fixture
        $managingAdvisorData = $this->findRecordsFromFixtureWhere(['user_role_id' => 2], UserFixture::class)[0];
        $advisorData = $this->findRecordsFromFixtureWhere(['user_role_id' => 3], UserFixture::class)[0];
        $newcomerData = $this->findRecordsFromFixtureWhere(['user_role_id' => 4], UserFixture::class)[0];

        $expectedSuccessfulJsonResponse = [
            StatusCodeInterface::class => StatusCodeInterface::STATUS_CREATED,
            'db_entry_created' => true,
            'json_response' => [
                'status' => 'success',
                'data' => null,
            ],
        ];
        $expectedUnauthorizedJsonResponse = [
            StatusCodeInterface::class => StatusCodeInterface::STATUS_FORBIDDEN,
            'db_entry_created' => false,
            'json_response' => [
                'status' => 'error',
                'message' => 'Not allowed to create a client.',
            ]
        ];
        return [
            [ // ? Authenticated user is newcomer - not allowed
                'user_linked_to_client' => $newcomerData,
                'authenticated_user' => $newcomerData,
                'expected_result' => $expectedUnauthorizedJsonResponse
            ],
            [ // ? Authenticated user is advisor and linked user_id authenticated user - allowed
                'user_linked_to_client' => $advisorData,
                'authenticated_user' => $advisorData,
                'expected_result' => $expectedSuccessfulJsonResponse,
            ],
            [ // ? Authenticated user is advisor but linked user_id is not authenticated user - not allowed
                'user_linked_to_client' => $newcomerData,
                'authenticated_user' => $advisorData,
                'expected_result' => $expectedUnauthorizedJsonResponse,
            ],
            [ // ? Authenticated user is managing advisor and linked user_id is not authenticated user - allowed
                'user_linked_to_client' => $advisorData,
                'authenticated_user' => $managingAdvisorData,
                'expected_result' => $expectedSuccessfulJsonResponse,
            ],

        ];
    }
}