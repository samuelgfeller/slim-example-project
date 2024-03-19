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
            'dbChanged' => true,
            'jsonResponse' => [
                'status' => 'success',
                'data' => null, // age added in test function if present in request data
            ],
        ];
        $unauthorizedResult = [
            StatusCodeInterface::class => StatusCodeInterface::STATUS_FORBIDDEN,
            'dbChanged' => false,
            'jsonResponse' => [
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
                'userLinkedToClientRow' => $newcomerAttr,
                'authenticatedUserRow' => $newcomerAttr,
                // Data to be changed
                'requestData' => ['first_name' => 'value'],
                'expectedResult' => $unauthorizedResult,
            ],
            // * Advisor
            'advisor owner client status' => [ // ? Advisor owner - change client status - allowed
                'userLinkedToClientRow' => $advisorAttr,
                'authenticatedUserRow' => $advisorAttr,
                // client_status_id contains a temporary value replaced by the test function
                // Data to be changed
                'requestData' => array_merge(['client_status_id' => 'new'], $personalInfoChanges),
                'expectedResult' => $authorizedResult,
            ],
            'advisor owner assigned user' => [ // ? Advisor owner - change assigned user - not allowed
                'userLinkedToClientRow' => $advisorAttr,
                'authenticatedUserRow' => $advisorAttr,
                // user_id contains a temporary value replaced by the test function
                // Data to be changed
                'requestData' => ['user_id' => 'new'],
                'expectedResult' => $unauthorizedResult,
            ],
            'advisor not owner personal info' => [ // ? Advisor not owner - change personal info - allowed
                'userLinkedToClientRow' => $managingAdvisorAttr,
                'authenticatedUserRow' => $advisorAttr,
                // Data to be changed
                'requestData' => $personalInfoChanges,
                'expectedResult' => $authorizedResult,
            ],
            'advisor not owner client status' => [ // ? Advisor not owner - change client status - not allowed
                'userLinkedToClientRow' => $managingAdvisorAttr,
                'authenticatedUserRow' => $advisorAttr,
                // Data to be changed
                'requestData' => ['client_status_id' => 'new'],
                'expectedResult' => $unauthorizedResult,
            ],
            'advisor owner undelete' => [ // ? Advisor owner - undelete client - not allowed
                'userLinkedToClientRow' => $newcomerAttr,
                'authenticatedUserRow' => $advisorAttr,
                // Data to be changed
                'requestData' => ['deleted_at' => null],
                'expectedResult' => $unauthorizedResult,
            ],
            // * Managing advisor
            'managing advisor not owner all changes' => [ // ? Managing advisor not owner - change all data - allowed
                'userLinkedToClientRow' => $advisorAttr,
                'authenticatedUserRow' => $managingAdvisorAttr,
                // Data to be changed
                'requestData' => array_merge(
                    $personalInfoChanges,
                    ['client_status_id' => 'new', 'user_id' => 'new']
                ),
                'expectedResult' => $authorizedResult,
            ],
            'managing advisor not owner undelete' => [ // ? Managing advisor not owner - undelete client - allowed
                'userLinkedToClientRow' => $advisorAttr,
                'authenticatedUserRow' => $managingAdvisorAttr,
                // Data to be changed
                'requestData' => ['deleted_at' => null],
                'expectedResult' => $authorizedResult,
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
                'requestBody' => [
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
                'jsonResponse' => [
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
                'requestBody' => [
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
                'jsonResponse' => [
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
