<?php

namespace App\Test\Provider\User;

use App\Test\Fixture\FixtureTrait;
use Fig\Http\Message\StatusCodeInterface;

class UserUpdateCaseProvider
{

    use FixtureTrait;

    /**
     * User update authorization cases
     * Provides combination of different user roles with expected result.
     *
     * @return array[]
     */
    public function provideUserPasswordChangeAuthorizationCases(): array
    {
        // Password hash to verify old password - 12345678 is used in test function
        $passwordHash = password_hash('12345678', PASSWORD_DEFAULT);
        // Set different user role attributes
        $adminAttr = ['user_role_id' => 1, 'password_hash' => $passwordHash];
        $managingAdvisorAttr = ['user_role_id' => 2, 'password_hash' => $passwordHash];
        // If one attribute is different they are differentiated and 2 separated users are added to the db
        $otherManagingAdvisorAttr = ['first_name' => 'Other', 'user_role_id' => 2, 'password_hash' => $passwordHash];
        $advisorAttr = ['user_role_id' => 3, 'password_hash' => $passwordHash];
        $newcomerAttr = ['user_role_id' => 4, 'password_hash' => $passwordHash];

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
                'message' => 'Not allowed to change password.',
            ]
        ];


        // To avoid testing each column separately for each user role, the most basic change is taken to test.
        // [foreign_key => 'new'] will be replaced in test function as user has to be added to the database first
        return [
            // * Newcomer
            // "owner" means from the perspective of the authenticated user
            [ // ? Newcomer owner - allowed
                'user_to_change' => $newcomerAttr,
                'authenticated_user' => $newcomerAttr,
                'expected_result' => $authorizedResult
            ],
            // Higher privilege owner than newcomer must not be tested as authorization is hierarchical meaning if
            // the lowest privilege is allowed to do action, higher will be able too.
            // * Advisor
            [ // ? Advisor not owner - user to change is newcomer - not allowed
                'user_to_change' => $newcomerAttr,
                'authenticated_user' => $advisorAttr,
                'expected_result' => $unauthorizedResult,
            ],
            // * Managing advisor
            [ // ? Managing advisor not owner - user to change is advisor - allowed
                'user_to_change' => $advisorAttr,
                'authenticated_user' => $managingAdvisorAttr,
                'expected_result' => $authorizedResult,
            ],
            [ // ? Managing advisor not owner - user to change is other managing advisor - not allowed
                'user_to_change' => $otherManagingAdvisorAttr,
                'authenticated_user' => $managingAdvisorAttr,
                'expected_result' => $unauthorizedResult,
            ],
            // * Admin
            [ // ? Admin not owner - user to change is managing advisor - allowed
                'user_to_change' => $managingAdvisorAttr,
                'authenticated_user' => $adminAttr,
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
                    'location' => 'L',
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