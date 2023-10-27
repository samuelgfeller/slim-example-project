<?php

namespace App\Test\Provider\User;

use App\Domain\User\Enum\UserRole;
use App\Domain\User\Enum\UserStatus;
use App\Test\Traits\FixtureTestTrait;
use Fig\Http\Message\StatusCodeInterface;

class UserCreateProvider
{
    use FixtureTestTrait;

    /**
     * @return array[]
     */
    public static function userCreateAuthorizationCases(): array
    {
        // Set different user role attributes
        $managingAdvisorAttr = ['user_role_id' => UserRole::MANAGING_ADVISOR];
        $adminAttr = ['user_role_id' => UserRole::ADMIN];
        $advisorAttr = ['user_role_id' => UserRole::ADVISOR];
        $newcomerAttr = ['user_role_id' => UserRole::NEWCOMER];

        $authorizedResult = [
            StatusCodeInterface::class => StatusCodeInterface::STATUS_CREATED,
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
                'message' => 'Not allowed to create user.',
            ],
        ];

        // Lower privilege must not be tested as authorization is hierarchical meaning if given privilege is
        // not allowed to do action, lower will not be able to too. Same is for higher privilege but reversed.
        return [
            // * Advisor is the highest privilege that is not allowed to create user
            [ // ? Advisor - create newcomer - not allowed
                'authenticated_user' => $advisorAttr,
                'user_role_of_new_user' => UserRole::NEWCOMER,
                'expected_result' => $unauthorizedResult,
            ],
            // * Managing advisor
            [ // ? Managing advisor - create user with role advisor (the highest allowed role) - allowed
                'authenticated_user' => $managingAdvisorAttr,
                'user_role_of_new_user' => UserRole::ADVISOR,
                'expected_result' => $authorizedResult,
            ],
            [ // ? Managing advisor - create user with role managing advisor (the lowest not allowed) - not allowed
                'authenticated_user' => $managingAdvisorAttr,
                'user_role_of_new_user' => UserRole::MANAGING_ADVISOR,
                'expected_result' => $unauthorizedResult,
            ],
            // * Admin
            [ // ? Admin - create user with role admin - allowed
                'authenticated_user' => $adminAttr,
                'user_role_of_new_user' => UserRole::ADMIN,
                'expected_result' => $authorizedResult,
            ],
        ];
    }

    /**
     * Returns combinations of invalid data to trigger validation exception.
     *
     * @return array
     */
    public static function invalidUserCreateCases(): array
    {
        /** Same values as @see UserUpdateProvider::invalidUserUpdateCases() but with password and password2 */
        // Including as many values as possible that trigger validation errors in each case
        return [
            [
                'request_body' => [
                    // Values too short
                    'first_name' => 'n',
                    'surname' => 'n',
                    'email' => 'new.email@tes$t.ch',
                    'status' => 'non-existing',
                    'user_role_id' => 99,
                    'password' => '12',
                    'password2' => '1',
                ],
                'json_response' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'errors' => [
                            'first_name' => [0 => 'Minimum length is 2'],
                            'surname' => [0 => 'Minimum length is 2'],
                            'email' => [0 => 'Invalid email'],
                            'status' => [0 => 'Invalid option'],
                            'user_role_id' => [0 => 'Invalid option'],
                            'password' => [0 => 'Minimum length is 3'],
                            'password2' => [0 => 'Minimum length is 3', 1 => 'Passwords do not match'],
                        ],
                    ],
                ],
            ],
            [
                // Values too long
                'request_body' => [
                    'first_name' => str_repeat('i', 101),
                    'surname' => str_repeat('i', 101),
                    'email' => 'new.email.@test.ch',
                    // Valid required values to test the above
                    'status' => UserStatus::Active->value,
                    'user_role_id' => 1,
                    'password' => '12345678',
                    'password2' => '12345678',
                ],
                'json_response' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'errors' => [
                            'first_name' => [0 => 'Maximum length is 100'],
                            'surname' => [0 => 'Maximum length is 100'],
                            'email' => [0 => 'Invalid email'],
                        ],
                    ],
                ],
            ],
            [
                // Required values not given
                'request_body' => [
                    'first_name' => '',
                    'surname' => '',
                    'email' => '',
                    // Valid required values to test the above
                    'status' => '',
                    'user_role_id' => '',
                    'password' => '',
                    'password2' => '',
                ],
                'json_response' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'errors' => [
                            'first_name' => [0 => 'Minimum length is 2'],
                            'surname' => [0 => 'Minimum length is 2'],
                            'email' => [0 => 'Invalid email'],
                            'status' => [0 => 'Invalid option'],
                            // Same error message twice because not numeric and not existing
                            'user_role_id' => [0 => 'Invalid option', 1 => 'Invalid option'],
                            'password' => [0 => 'Password required'],
                            'password2' => [0 => 'Password required'],
                        ],
                    ],
                ],
            ],
            [// Test with empty request body
                'request_body' => [],
                'json_response' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'errors' => [
                            'first_name' => [0 => 'Key is required'],
                            'surname' => [0 => 'Key is required'],
                            'email' => [0 => 'Key is required'],
                            'status' => [0 => 'Key is required'],
                            'user_role_id' => [0 => 'Key is required'],
                            'password' => [0 => 'Key is required'],
                            'password2' => [0 => 'Key is required'],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Provide cases of malformed request body.
     *
     * @return array[]
     */
    public static function malformedRequestBodyCases(): array
    {
        return [
            [
                'empty_body' => [],
            ],
            [
                'null_body' => null,
            ],
            [
                // If any of the list except is missing it's a bad request
                'missing_first_name' => [
                    // Missing first name
                    'surname' => 'value',
                    'email' => 'value',
                    'status' => 'value',
                    'user_role_id' => 'value',
                    'password' => 'value',
                    'password2' => 'value',
                ],
            ],
            [
                'key_too_much' => [
                    'first_name' => 'value',
                    'surname' => 'value',
                    'email' => 'value',
                    'status' => 'value',
                    'user_role_id' => 'value',
                    'password' => 'value',
                    'password2' => 'value',
                    'other_random_key' => 'value',
                ],
            ],
        ];
    }
}
