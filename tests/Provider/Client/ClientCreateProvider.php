<?php

namespace App\Test\Provider\Client;

use App\Domain\User\Enum\UserRole;
use App\Test\Traits\FixtureTestTrait;
use Fig\Http\Message\StatusCodeInterface;

class ClientCreateProvider
{
    use FixtureTestTrait;

    /**
     * Provide malformed request body for client creation.
     *
     * @return array[]
     */
    public function malformedRequestBodyCases(): array
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
                    'message' => 'value', // main note
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
                    'message' => 'value',
                    'key_too_much' => 'value',
                ],
            ],
        ];
    }

    /**
     * Client creation authorization
     * Provides combination of different user roles with expected result.
     * This tests the rules in ClientAuthorizationChecker.
     *
     * @return array[]
     */
    public function clientCreationUsersAndExpectedResultProvider(): array
    {
        // Get users with different roles
        $managingAdvisorAttributes = ['user_role_id' => UserRole::MANAGING_ADVISOR];
        $advisorAttributes = ['user_role_id' => UserRole::ADVISOR];
        $newcomerAttributes = ['user_role_id' => UserRole::NEWCOMER];

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
                'message' => 'Not allowed to create client.',
            ],
        ];

        return [
            // "owner" means from the perspective of the authenticated user
            [ // ? Newcomer owner - not allowed
                'user_linked_to_client' => $newcomerAttributes,
                'authenticated_user' => $newcomerAttributes,
                'expected_result' => $unauthorizedResult,
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

    /**
     * Returns combinations of invalid data to trigger validation exception
     * for client creation. Couldn't be combined with update as creation
     * form includes main note message field.
     *
     * @return array
     */
    public function invalidClientCreationValuesAndExpectedResponseProvider(): array
    {
        // Including as many values as possible that trigger validation errors in each case
        return [
            [
                // Most values too short
                'request_body' => [
                    'first_name' => 'T',
                    'last_name' => 'A',
                    'birthdate' => '1850-01-01', // too old
                    'location' => 'L',
                    'phone' => '07',
                    'email' => 'test@test', // missing extension
                    'sex' => 'A', // invalid value
                    'user_id' => '999', // non-existing user
                    'client_status_id' => '999', // non-existing status
                    'message' => '', // valid vor now as note validation is done only if all client values are valid
                ],
                'json_response' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'message' => 'There is something in the client data that couldn\'t be validated',
                        'errors' => [
                            0 => [
                                'field' => 'client_status',
                                'message' => 'Client status not existing',
                            ],
                            1 => [
                                'field' => 'user',
                                'message' => 'User not existing',
                            ],
                            2 => [
                                'field' => 'first_name',
                                'message' => 'Minimum length is 2',
                            ],
                            3 => [
                                'field' => 'last_name',
                                'message' => 'Minimum length is 2',
                            ],
                            4 => [
                                'field' => 'email',
                                'message' => 'Invalid email address',
                            ],
                            5 => [
                                'field' => 'birthdate',
                                'message' => 'Invalid birthdate',
                            ],
                            6 => [
                                'field' => 'location',
                                'message' => 'Minimum length is 2',
                            ],
                            7 => [
                                'field' => 'phone',
                                'message' => 'Minimum length is 3',
                            ],
                            8 => [
                                'field' => 'sex',
                                'message' => 'Invalid sex value given.',
                            ],
                        ],
                    ],
                ],
            ],
            [
                // Most values too long
                'request_body' => [
                    'first_name' => str_repeat('i', 101), // 101 chars
                    'last_name' => str_repeat('i', 101),
                    'birthdate' => (new \DateTime())->modify('+1 day')->format('Y-m-d'), // 1 day in the future
                    'location' => str_repeat('i', 101),
                    'phone' => '+41 0071 121 12 12 12', // 21 chars
                    'email' => 'test$@test.ch', // invalid email
                    'sex' => '', // empty string
                    // All keys are needed as same dataset is used for create which always expects all keys
                    // and the json_response has to be equal too so the value can't be null.
                    'user_id' => '999', // non-existing user
                    'client_status_id' => '999', // non-existing status
                    'message' => '', // valid vor now as note validation is done only if all client values are valid
                ],
                'json_response' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'message' => 'There is something in the client data that couldn\'t be validated',
                        'errors' => [
                            0 => [
                                'field' => 'client_status',
                                'message' => 'Client status not existing',
                            ],
                            1 => [
                                'field' => 'user',
                                'message' => 'User not existing',
                            ],
                            2 => [
                                'field' => 'first_name',
                                'message' => 'Maximum length is 100',
                            ],
                            3 => [
                                'field' => 'last_name',
                                'message' => 'Maximum length is 100',
                            ],
                            4 => [
                                'field' => 'birthdate',
                                'message' => 'Invalid birthdate',
                            ],
                            5 => [
                                'field' => 'location',
                                'message' => 'Maximum length is 100',
                            ],
                            6 => [
                                'field' => 'phone',
                                'message' => 'Maximum length is 20',
                            ],
                        ],
                    ],
                ],
            ],
            [ // Main note validation
                // All client values valid but not main note message
                'request_body' => [
                    'first_name' => 'Test',
                    'last_name' => 'test',
                    'birthdate' => '1950-01-01',
                    'location' => 'Basel',
                    'phone' => '0771111111',
                    'email' => 'test@test.ch',
                    'sex' => 'F',
                    'user_id' => 'valid', // 'valid' replaced by authenticated user id in test function
                    'client_status_id' => 'valid', // 'valid' replaced by inserted client status id in test function
                    'message' => str_repeat('i', 1001), // invalid
                ],
                'json_response' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'message' => 'There is something in the note data that couldn\'t be validated',
                        'errors' => [
                            0 => [
                                'field' => 'message',
                                'message' => 'Maximum length is 1000',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
