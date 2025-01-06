<?php

namespace App\Test\Provider\Client;

use App\Module\User\Enum\UserRole;
use Fig\Http\Message\StatusCodeInterface;

class ClientCreateProvider
{
    /**
     * Provides test cases for client creation dropdown options.
     *
     * Each test case is an array with the following structure:
     * - 'authenticatedUserRow': An array of attributes for the authenticated user. This includes 'first_name' and 'user_role_id'.
     * - 'otherUserRow': An array of attributes for another user. This includes 'first_name' and 'user_role_id'.
     * - 'expectedUserNames': An array of expected names. This is used to verify the output of the function being tested.
     *
     * @return array an array of test cases
     */
    public static function clientCreationDropdownOptionsCases(): array
    {
        // Set users with different roles
        $managingAdvisorAttributes = ['first_name' => 'Manager', 'user_role_id' => UserRole::MANAGING_ADVISOR];
        $advisorAttributes = ['first_name' => 'Advisor', 'user_role_id' => UserRole::ADVISOR];
        $newcomerAttributes = ['first_name' => 'Newcomer', 'user_role_id' => UserRole::NEWCOMER];

        // Newcomer must not receive any available user
        // Advisor is allowed to create a client but only assign it to himself or leave user_id empty
        // Managing advisor and higher should receive all available users
        return [
            // "owner" means from the perspective of the authenticated user
            [ // ? Newcomer - not allowed, so nothing should be returned
                'authenticatedUserRow' => $newcomerAttributes,
                'otherUserRow' => $advisorAttributes,
                'expectedUserNames' => [],
            ],
            [ // ? Advisor - should return only himself
                'authenticatedUserRow' => $advisorAttributes,
                'otherUserRow' => $newcomerAttributes,
                // id not relevant only name
                'expectedUserNames' => [$advisorAttributes['first_name']],
            ],
            [ // ? Managing advisor - should return all available users
                'authenticatedUserRow' => $managingAdvisorAttributes,
                'otherUserRow' => $newcomerAttributes,
                // All available users are authenticated manager advisor and newcomer as the "other" user
                'expectedUserNames' => [$managingAdvisorAttributes['first_name'], $newcomerAttributes['first_name']],
            ],
        ];
    }

    /**
     * Client creation authorization
     * Provides a combination of different user roles with expected result.
     * This tests the rules in ClientAuthorizationChecker.
     *
     * @return array
     */
    public static function clientCreationAuthorizationProvider(): array
    {
        // Get users with different roles
        $managingAdvisorAttributes = ['user_role_id' => UserRole::MANAGING_ADVISOR];
        $advisorAttributes = ['user_role_id' => UserRole::ADVISOR];
        $newcomerAttributes = ['user_role_id' => UserRole::NEWCOMER];

        $authorizedResult = [
            StatusCodeInterface::class => StatusCodeInterface::STATUS_CREATED,
            'db_entry_created' => true,
            'jsonResponse' => [
                'status' => 'success',
                'data' => null,
            ],
        ];
        $unauthorizedResult = [
            StatusCodeInterface::class => StatusCodeInterface::STATUS_FORBIDDEN,
            'db_entry_created' => false,
            'jsonResponse' => [
                'status' => 'error',
                'message' => 'Not allowed to create client.',
            ],
        ];

        return [
            // "owner" means from the perspective of the authenticated user
            [ // ? Newcomer owner - not allowed
                'userLinkedToClientRow' => $newcomerAttributes,
                'authenticatedUserRow' => $newcomerAttributes,
                'expectedResult' => $unauthorizedResult,
            ],
            [ // ? Advisor owner - allowed
                'userLinkedToClientRow' => $advisorAttributes,
                'authenticatedUserRow' => $advisorAttributes,
                'expectedResult' => $authorizedResult,
            ],
            [ // ? Advisor - client assigned to no one - allowed
                'userLinkedToClientRow' => null,
                'authenticatedUserRow' => $advisorAttributes,
                'expectedResult' => $authorizedResult,
            ],
            [ // ? Advisor not owner - not allowed
                'userLinkedToClientRow' => $newcomerAttributes,
                'authenticatedUserRow' => $advisorAttributes,
                'expectedResult' => $unauthorizedResult,
            ],
            [ // ? Managing not owner - allowed
                'userLinkedToClientRow' => $advisorAttributes,
                'authenticatedUserRow' => $managingAdvisorAttributes,
                'expectedResult' => $authorizedResult,
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
    public static function invalidClientCreationProvider(): array
    {
        // Including as many values as possible that trigger validation errors in each case
        return [
            [
                // Most values too short, birthdate too old and user_id has 2 validation error messages
                'requestBody' => [
                    'first_name' => 'T',
                    'last_name' => 'A',
                    'birthdate' => '1850-01-01', // over 130 years old
                    'location' => 'L',
                    'phone' => '07',
                    'email' => 'test@test', // missing extension
                    'sex' => 'A', // invalid value
                    'user_id' => 'a', // wrong format and non-existing user
                    'client_status_id' => 'a', // wrong format and non-existing status
                    'message' => '', // valid vor now as note validation is done only if all client values are valid
                ],
                'jsonResponse' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'errors' => [
                            'first_name' => [0 => 'Minimum length is 2'],
                            'last_name' => [0 => 'Minimum length is 2'],
                            'email' => [0 => 'Invalid email'],
                            'birthdate' => [0 => 'Cannot be older than 130 years'],
                            'location' => [0 => 'Minimum length is 2'],
                            'phone' => [0 => 'Minimum length is 3'],
                            'sex' => [0 => 'Invalid option'],
                            'client_status_id' => [0 => 'Invalid option format', 1 => 'Invalid option'],
                            'user_id' => [0 => 'Invalid option format', 1 => 'Invalid option'],
                        ],
                    ],
                ],
            ],
            [
                // Most values too long, birthdate in the future
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
                    'message' => '', // valid vor now as note validation is done only if all client values are valid
                ],
                'jsonResponse' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'errors' => [
                            'first_name' => [0 => 'Maximum length is 100'],
                            'last_name' => [0 => 'Maximum length is 100'],
                            'birthdate' => [0 => 'Cannot be in the future'],
                            'location' => [0 => 'Maximum length is 100'],
                            'phone' => [0 => 'Maximum length is 20'],
                            'client_status_id' => [0 => 'Invalid option'],
                            'user_id' => [0 => 'Invalid option'],
                        ],
                    ],
                ],
            ],
            [ // Main note validation when user creates a new client directly with a main note
                // All client values valid but not main note message
                'requestBody' => [
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
                'jsonResponse' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'errors' => [
                            'message' => [0 => 'Maximum length is 1000'],
                        ],
                    ],
                ],
            ],
            [ // Check for request body key presence (previously done via malformedBodyRequestChecker)
                // Empty request body
                'requestBody' => [],
                'jsonResponse' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'errors' => [
                            'first_name' => [0 => 'Field is required'],
                            'last_name' => [0 => 'Field is required'],
                            'email' => [0 => 'Field is required'],
                            'birthdate' => [0 => 'Field is required'],
                            'location' => [0 => 'Field is required'],
                            'phone' => [0 => 'Field is required'],
                            'client_status_id' => [0 => 'Field is required'],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Returns combinations of valid client creation data to assert that
     * validation doesn't fail.
     * The reason for this test is that cakephp validation library treats null values
     * as invalid when a validation method is set on a field.
     * E.g. ->maxLength('first_name', 100) has the consequence that it expects
     * a non-null value for the first_name. Without ->allowEmptyString('first_name')
     * the validation would fail with "This field cannot be left empty".
     * I did not expect this behaviour and ran into this when testing, so this test
     * * reminds to always add ->allow[Whatever] when value is optional.
     *
     * @return array
     */
    public static function validClientCreationProvider(): array
    {
        return [
            [
                // Test with null values on all optional fields (either first_name or last_name has to be set)
                'requestBody' => [
                    'first_name' => 'First name',
                    'last_name' => null,
                    'birthdate' => null,
                    'location' => null,
                    'phone' => null,
                    'email' => null,
                    'sex' => null,
                    'user_id' => null,
                    'client_status_id' => 'valid', // 'valid' replaced by inserted client status id in test function
                    'message' => null,
                ],
            ],
            [
                // Test with empty string values on all optional fields (either first_name or last_name has to be set)
                'requestBody' => [
                    'first_name' => '',
                    'last_name' => '',
                    'birthdate' => '',
                    'location' => '',
                    'phone' => '',
                    'email' => '',
                    'sex' => '',
                    'user_id' => '',
                    'client_status_id' => 'valid', // 'valid' replaced by inserted client status id in test function
                    'message' => '',
                ],
            ],
        ];
    }
}
