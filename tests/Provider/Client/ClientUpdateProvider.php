<?php

namespace App\Test\Provider\Client;

use App\Domain\User\Enum\UserRole;
use Fig\Http\Message\StatusCodeInterface;

class ClientUpdateProvider
{
    /**
     * Client creation authorization
     * Provides combination of different user roles with expected result.
     * This tests the rules in ClientAuthorizationChecker.
     *
     * @return array[]
     */
    public static function clientUpdateUsersAndExpectedResultProvider(): array
    {
        // Set different user role attributes
        $managingAdvisorAttr = ['user_role_id' => UserRole::MANAGING_ADVISOR];
        $advisorAttr = ['user_role_id' => UserRole::ADVISOR];
        $newcomerAttr = ['user_role_id' => UserRole::NEWCOMER];

        $basicClientDataChanges = [
            'first_name' => 'NewFirstName',
            'last_name' => 'NewLastName',
            'birthdate' => '1999-10-22',
            'location' => 'NewLocation',
            'phone' => '011 111 11 11',
            'email' => 'new.email@test.ch',
            'sex' => 'O',
        ];

        $authorizedResult = [
            StatusCodeInterface::class => StatusCodeInterface::STATUS_OK,
            'db_changed' => true,
            'json_response' => [
                'status' => 'success',
                'data' => null, // age added in test function if present in request data
            ],
        ];
        $unauthorizedResult = [
            StatusCodeInterface::class => StatusCodeInterface::STATUS_FORBIDDEN,
            'db_changed' => false,
            'json_response' => [
                'status' => 'error',
                'message' => 'Not allowed to update client.',
            ],
        ];

        // To avoid testing each column separately for each user role, the most basic change is taken to test.
        // [foreign_key => 'new'] will be replaced in test function as user has to be added to the database first.
        return [
            // * Newcomer
            // "owner" means from the perspective of the authenticated user
            [ // ? Newcomer owner - data to be changed is the one with the least privilege needed - not allowed
                'user_linked_to_client' => $newcomerAttr,
                'authenticated_user' => $newcomerAttr,
                'data_to_be_changed' => ['first_name' => 'value'],
                'expected_result' => $unauthorizedResult,
            ],
            // * Advisor
            [ // ? Advisor owner - data to be changed allowed
                'user_linked_to_client' => $advisorAttr,
                'authenticated_user' => $advisorAttr,
                'data_to_be_changed' => array_merge(['client_status_id' => 'new'], $basicClientDataChanges),
                'expected_result' => $authorizedResult,
            ],
            [ // ? Advisor owner - data to be changed not allowed
                'user_linked_to_client' => $advisorAttr,
                'authenticated_user' => $advisorAttr,
                'data_to_be_changed' => ['user_id' => 'new'],
                'expected_result' => $unauthorizedResult,
            ],
            [ // ? Advisor not owner - data to be changed allowed
                'user_linked_to_client' => $managingAdvisorAttr,
                'authenticated_user' => $advisorAttr,
                'data_to_be_changed' => $basicClientDataChanges,
                'expected_result' => $authorizedResult,
            ],
            [ // ? Advisor not owner - data to be changed not allowed
                'user_linked_to_client' => $managingAdvisorAttr,
                'authenticated_user' => $advisorAttr,
                'data_to_be_changed' => ['client_status_id' => 'new'],
                'expected_result' => $unauthorizedResult,
            ],
            [ // ? Advisor owner - undelete client - not allowed
                'user_linked_to_client' => $newcomerAttr,
                'authenticated_user' => $advisorAttr,
                'data_to_be_changed' => ['deleted_at' => null],
                'expected_result' => $unauthorizedResult,
            ],

            // * Managing advisor
            [ // ? Managing advisor not owner - there is no data change that is not allowed for managing advisor
                'user_linked_to_client' => $advisorAttr,
                'authenticated_user' => $managingAdvisorAttr,
                'data_to_be_changed' => array_merge(
                    $basicClientDataChanges,
                    ['client_status_id' => 'new', 'user_id' => 'new']
                ),
                'expected_result' => $authorizedResult,
            ],
            [ // ? Managing advisor not owner - undelete client - allowed
                'user_linked_to_client' => $advisorAttr,
                'authenticated_user' => $managingAdvisorAttr,
                'data_to_be_changed' => ['deleted_at' => null],
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
    public static function invalidClientUpdateProvider(): array
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
                ],
                'json_response' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'errors' => [
                            'first_name' => [
                                0 => 'Minimum length is 2',
                            ],
                            'last_name' => [
                                0 => 'Minimum length is 2',
                            ],
                            'email' => [
                                0 => 'Invalid email',
                            ],
                            'birthdate' => [
                                0 => 'Cannot be older than 130 years',
                            ],
                            'location' => [
                                0 => 'Minimum length is 2',
                            ],
                            'phone' => [
                                0 => 'Minimum length is 3',
                            ],
                            'sex' => [
                                0 => 'Invalid option',
                            ],
                            'client_status_id' => [
                                0 => 'Invalid option',
                            ],
                            'user_id' => [
                                0 => 'Invalid option',
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
                ],
                'json_response' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'errors' => [
                            'first_name' => [
                                0 => 'Maximum length is 100',
                            ],
                            'last_name' => [
                                0 => 'Maximum length is 100',
                            ],
                            'birthdate' => [
                                0 => 'Cannot be in the future',
                            ],
                            'location' => [
                                0 => 'Maximum length is 100',
                            ],
                            'phone' => [
                                0 => 'Maximum length is 20',
                            ],
                            'client_status_id' => [
                                0 => 'Invalid option',
                            ],
                            'user_id' => [
                                0 => 'Invalid option',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
