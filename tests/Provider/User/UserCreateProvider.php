<?php

namespace App\Test\Provider\User;

use App\Domain\User\Enum\UserRole;
use App\Domain\User\Enum\UserStatus;
use Fig\Http\Message\StatusCodeInterface;

class UserCreateProvider
{
    public static function userCreateAuthorizationCases(): array
    {
        // Set different user role attributes
        $managingAdvisorAttr = ['user_role_id' => UserRole::MANAGING_ADVISOR];
        $adminAttr = ['user_role_id' => UserRole::ADMIN];
        $advisorAttr = ['user_role_id' => UserRole::ADVISOR];
        $newcomerAttr = ['user_role_id' => UserRole::NEWCOMER];

        $authorizedResult = [
            StatusCodeInterface::class => StatusCodeInterface::STATUS_CREATED,
            'dbChanged' => true,
            'jsonResponse' => [
                'status' => 'success',
                'data' => null,
            ],
        ];
        $unauthorizedResult = [
            StatusCodeInterface::class => StatusCodeInterface::STATUS_FORBIDDEN,
            'dbChanged' => false,
            'jsonResponse' => [
                'status' => 'error',
                'message' => 'Not allowed to create user.',
            ],
        ];

        // Lower privilege must not be tested as authorization is hierarchical meaning if given privilege is
        // not allowed to do action, lower will not be able to too. Same is for higher privilege but reversed.
        return [
            // * Advisor is the highest privilege not allowed to create user
            [ // ? Advisor - create newcomer - not allowed
                'authenticatedUserAttr' => $advisorAttr,
                'newUserRole' => UserRole::NEWCOMER,
                'expectedResult' => $unauthorizedResult,
            ],
            // * Managing advisor
            [ // ? Managing advisor - create user with role advisor (the highest allowed role) - allowed
                'authenticatedUserAttr' => $managingAdvisorAttr,
                'newUserRole' => UserRole::ADVISOR,
                'expectedResult' => $authorizedResult,
            ],
            [ // ? Managing advisor - create user with role managing advisor (the lowest not allowed) - not allowed
                'authenticatedUserAttr' => $managingAdvisorAttr,
                'newUserRole' => UserRole::MANAGING_ADVISOR,
                'expectedResult' => $unauthorizedResult,
            ],
            // * Admin
            [ // ? Admin - create user with role admin - allowed
                'authenticatedUserAttr' => $adminAttr,
                'newUserRole' => UserRole::ADMIN,
                'expectedResult' => $authorizedResult,
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
                'requestBody' => [
                    // Values too short
                    'first_name' => 'n',
                    'surname' => 'n',
                    'email' => 'new.email@tes$t.ch',
                    'status' => 'non-existing',
                    'user_role_id' => 99,
                    'password' => '12',
                    'password2' => '1',
                ],
                'jsonResponse' => [
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
                'requestBody' => [
                    'first_name' => str_repeat('i', 101),
                    'surname' => str_repeat('i', 101),
                    'email' => 'new.email.@test.ch',
                    // Valid required values to test the above
                    'status' => UserStatus::Active->value,
                    'user_role_id' => 1,
                    'password' => '12345678',
                    'password2' => '12345678',
                ],
                'jsonResponse' => [
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
                'requestBody' => [
                    'first_name' => '',
                    'surname' => '',
                    'email' => '',
                    // Valid required values to test the above
                    'status' => '',
                    'user_role_id' => '',
                    'password' => '',
                    'password2' => '',
                ],
                'jsonResponse' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'errors' => [
                            'first_name' => [0 => 'Required'],
                            'surname' => [0 => 'Required'],
                            'email' => [0 => 'Invalid email'],
                            'status' => [0 => 'Invalid option'],
                            // Same error message twice because not numeric and not existing.
                            // Authenticated user is admin which means allowed to assign any role hence not failed authorization
                            'user_role_id' => [0 => 'Invalid option', 1 => 'Invalid option'],
                            'password' => [0 => 'Password required'],
                            'password2' => [0 => 'Password required'],
                        ],
                    ],
                ],
            ],
            [// Test with empty request body
                'requestBody' => [],
                'jsonResponse' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'errors' => [
                            'first_name' => [0 => 'Field is required'],
                            'surname' => [0 => 'Field is required'],
                            'email' => [0 => 'Field is required'],
                            'status' => [0 => 'Field is required'],
                            'user_role_id' => [0 => 'Field is required'],
                            'password' => [0 => 'Field is required'],
                            'password2' => [0 => 'Field is required'],
                        ],
                    ],
                ],
            ],
        ];
    }
}
