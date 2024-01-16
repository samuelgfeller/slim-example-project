<?php

namespace App\Test\Provider\Client;

use App\Domain\User\Enum\UserRole;
use Fig\Http\Message\StatusCodeInterface;

class ClientUpdateProvider
{
    /**
     * Client creation authorization
     * Provides the combination of different user roles with expected results.
     * This tests the rules in ClientAuthorizationChecker.
     *
     * @return array[]
     */
    public static function clientUpdateAuthorizationCases(): array
    {
        // Set different user role attributes
        $managingAdvisorAttr = ['user_role_id' => UserRole::MANAGING_ADVISOR];
        $advisorAttr = ['user_role_id' => UserRole::ADVISOR];
        $newcomerAttr = ['user_role_id' => UserRole::NEWCOMER];

        $personalInfoChanges = [
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
            'newcomer owner personal info' => [ // ? Newcomer owner - change first name - not allowed
                'user_linked_to_client' => $newcomerAttr,
                'authenticated_user' => $newcomerAttr,
                'data_to_be_changed' => ['first_name' => 'value'],
                'expected_result' => $unauthorizedResult,
            ],
            // * Advisor
            'advisor owner client status' => [ // ? Advisor owner - change client status - allowed
                'user_linked_to_client' => $advisorAttr,
                'authenticated_user' => $advisorAttr,
                // client_status_id contains a temporary value replaced by the test function
                'data_to_be_changed' => array_merge(['client_status_id' => 'new'], $personalInfoChanges),
                'expected_result' => $authorizedResult,
            ],
            'advisor owner assigned user' => [ // ? Advisor owner - change assigned user - not allowed
                'user_linked_to_client' => $advisorAttr,
                'authenticated_user' => $advisorAttr,
                // user_id contains a temporary value replaced by the test function
                'data_to_be_changed' => ['user_id' => 'new'],
                'expected_result' => $unauthorizedResult,
            ],
            'advisor not owner personal info' => [ // ? Advisor not owner - change personal info - allowed
                'user_linked_to_client' => $managingAdvisorAttr,
                'authenticated_user' => $advisorAttr,
                'data_to_be_changed' => $personalInfoChanges,
                'expected_result' => $authorizedResult,
            ],
            'advisor not owner client status' => [ // ? Advisor not owner - change client status - not allowed
                'user_linked_to_client' => $managingAdvisorAttr,
                'authenticated_user' => $advisorAttr,
                'data_to_be_changed' => ['client_status_id' => 'new'],
                'expected_result' => $unauthorizedResult,
            ],
            'advisor owner undelete' => [ // ? Advisor owner - undelete client - not allowed
                'user_linked_to_client' => $newcomerAttr,
                'authenticated_user' => $advisorAttr,
                'data_to_be_changed' => ['deleted_at' => null],
                'expected_result' => $unauthorizedResult,
            ],
            // * Managing advisor
            'managing advisor not owner all changes' => [ // ? Managing advisor not owner - change all data - allowed
                'user_linked_to_client' => $advisorAttr,
                'authenticated_user' => $managingAdvisorAttr,
                'data_to_be_changed' => array_merge(
                    $personalInfoChanges,
                    ['client_status_id' => 'new', 'user_id' => 'new']
                ),
                'expected_result' => $authorizedResult,
            ],
            'managing advisor not owner undelete' => [ // ? Managing advisor not owner - undelete client - allowed
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
