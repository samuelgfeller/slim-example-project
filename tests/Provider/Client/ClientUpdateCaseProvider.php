<?php

namespace App\Test\Provider\Client;

use App\Test\Fixture\FixtureTrait;
use App\Test\Fixture\UserFixture;
use Fig\Http\Message\StatusCodeInterface;

class ClientUpdateCaseProvider
{

    use FixtureTrait;

    /**
     * Client creation authorization
     * Provides combination of different user roles with expected result.
     * This tests the rules in ClientAuthorizationChecker.
     *
     * @return array[]
     */
    public function provideUsersAndExpectedResultForClientUpdate(): array
    {
        // Set different user role attributes
        $managingAdvisorRow = ['user_role_id' => 2];
        $advisorRow = ['user_role_id' => 3];
        $newcomerRow = ['user_role_id' => 4];

        $authorizedResult = [
            StatusCodeInterface::class => StatusCodeInterface::STATUS_OK,
            'db_changed' => true,
            'json_response' => [
                'status' => 'success',
                'data' => null,
            ],
        ];
        $unauthorizedResult = [
            StatusCodeInterface::class => StatusCodeInterface::STATUS_FORBIDDEN,
            'db_changed' => false,
            'json_response' => [
                'status' => 'error',
                'message' => 'Not allowed to update client.',
            ]
        ];

        $basicClientDataChanges = [
            'first_name' => 'NewFirstName',
            'last_name' => 'NewLastName',
            'birthdate' => '1999-10-22',
            'location' => 'NewLocation',
            'phone' => '011 111 11 11',
            'email' => 'new.email@test.ch',
            'sex' => 'O',
        ];

        // To avoid testing each column separately for each user role, the most basic change is taken to test
        // [foreign_key => 'new'] will be replaced in test function as user has to be added to the database
        return [
            // * Newcomer
            // User role and when "owner" is mentioned, it is always from the perspective of the authenticated user
            [ // ? Newcomer owner - data to be changed is the one with the least privilege needed - not allowed
                'user_linked_to_client' => $newcomerRow,
                'authenticated_user' => $newcomerRow,
                'data_to_be_changed' => ['first_name' => 'value'],
                'expected_result' => $unauthorizedResult
            ],
            // * Advisor
            [ // ? Advisor owner - data to be changed allowed
                'user_linked_to_client' => $advisorRow,
                'authenticated_user' => $advisorRow,
                'data_to_be_changed' => array_merge(['client_status_id' => 'new'], $basicClientDataChanges),
                'expected_result' => $authorizedResult,
            ],
            [ // ? Advisor owner - data to be changed not allowed
                'user_linked_to_client' => $advisorRow,
                'authenticated_user' => $advisorRow,
                'data_to_be_changed' => ['user_id' => 'new'],
                'expected_result' => $unauthorizedResult,
            ],
            [ // ? Advisor not owner - data to be changed allowed
                'user_linked_to_client' => $managingAdvisorRow,
                'authenticated_user' => $advisorRow,
                'data_to_be_changed' => $basicClientDataChanges,
                'expected_result' => $authorizedResult,
            ],
            [ // ? Advisor not owner - data to be changed not allowed
                'user_linked_to_client' => $managingAdvisorRow,
                'authenticated_user' => $advisorRow,
                'data_to_be_changed' => ['client_status_id' => 'new'],
                'expected_result' => $unauthorizedResult,
            ],

            // * Managing advisor
            [ // ? Managing advisor not owner - there is no data change that is not allowed for managing advisor
                'user_linked_to_client' => $advisorRow,
                'authenticated_user' => $managingAdvisorRow,
                'data_to_be_changed' => array_merge(
                    $basicClientDataChanges,
                    ['client_status_id' => 'new', 'user_id' => 'new']
                ),
                'expected_result' => $authorizedResult,
            ],
        ];
    }


    /**
     * Returns combinations of invalid data to trigger validation exception
     * for client modification.
     *
     * @return array
     */
    public function invalidClientUpdateValuesAndExpectedResponseData(): array
    {
        // The goal is to include as many values as possible that should trigger validation errors in each iteration
        return [
            [
                // Most values too short
                'request_body' => [
                    'first_name' => 'T',
                    'last_name' => 'A',
                    'birthdate' => '1850-01-01', // too old
                    'location' => 'La',
                    'phone' => '07',
                    'email' => 'test@test', // missing extension
                    'sex' => 'A', // invalid value
                    'user_id' => '999', // non-existing user
                    'client_status_id' => '999', // non-existing status
                ],
                'json_response' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'message' => 'There is something in the client data that couldn\'t be validated',
                        'errors' => [
                            0 => [
                                'field' => 'client_status',
                                'message' => 'Client_status not existing',
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
                                'message' => 'Minimum length is 3',
                            ],
                            7 => [
                                'field' => 'phone',
                                'message' => 'Minimum length is 3',
                            ],
                            8 => [
                                'field' => 'sex',
                                'message' => 'Invalid sex value given.',
                            ],
                        ]
                    ]
                ]
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
                ],
                'json_response' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'message' => 'There is something in the client data that couldn\'t be validated',
                        'errors' => [
                            0 => [
                                'field' => 'client_status',
                                'message' => 'Client_status not existing',
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
                        ]
                    ]
                ]
            ]
        ];
    }
}