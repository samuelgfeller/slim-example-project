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
    public function userCreateAuthorizationCases(): array
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
    public function invalidUserCreateCases(): array
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
                        'message' => 'There is a validation error when trying to register a user',
                        'errors' => [
                            ['field' => 'first_name', 'message' => 'Minimum length is 2'],
                            ['field' => 'surname', 'message' => 'Minimum length is 2'],
                            ['field' => 'email', 'message' => 'Invalid email address'],
                            // Technically the better error would be that the status is not existing but an UserData object
                            // instance is created that puts "null" if status is not existing before being passed to the validator
                            ['field' => 'status', 'message' => 'Status is required'],
                            ['field' => 'user_role', 'message' => 'User role not existing'],
                            ['field' => 'password2', 'message' => 'Passwords do not match'],
                            ['field' => 'password', 'message' => 'Minimum length is 3'],
                            ['field' => 'password2', 'message' => 'Minimum length is 3'],
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
                        'message' => 'There is a validation error when trying to register a user',
                        'errors' => [
                            ['field' => 'first_name', 'message' => 'Maximum length is 100'],
                            ['field' => 'surname', 'message' => 'Maximum length is 100'],
                            ['field' => 'email', 'message' => 'Invalid email address'],
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
                        'message' => 'There is a validation error when trying to register a user',
                        'errors' => [
                            ['field' => 'first_name', 'message' => 'Name is required'],
                            ['field' => 'surname', 'message' => 'Name is required'],
                            ['field' => 'email', 'message' => 'Email is required'],
                            ['field' => 'status', 'message' => 'Status is required'],
                            ['field' => 'user_role_id', 'message' => 'User role is required'],
                            ['field' => 'password', 'message' => 'Password is required'],
                            ['field' => 'password2', 'message' => 'Password is required'],
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
    public function malformedRequestBodyCases(): array
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
