<?php

namespace App\Test\Provider\User;

use App\Domain\User\Enum\UserRole;
use App\Test\Traits\FixtureTrait;
use Fig\Http\Message\StatusCodeInterface;

class UserCreateCaseProvider
{

    use FixtureTrait;

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
            ]
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
     * Returns combinations of invalid data to trigger validation exception
     * for modification.
     *
     * @return array
     */
    public function invalidUserUpdateCases(): array
    {
        // The goal is to include as many values as possible that should trigger validation errors in each iteration
        return [
            [
                'request_body' => [
                    // Values too short
                    'first_name' => 'n',
                    'surname' => 'n',
                    'email' => 'new.email@tes$t.ch',
                    'status' => 'non-existing',
                    'user_role_id' => 99,
                ],
                'json_response' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'message' => 'There is a validation error when trying to update a user',
                        'errors' => [
                            ['field' => 'first_name', 'message' => 'Minimum length is 2'],
                            ['field' => 'surname', 'message' => 'Minimum length is 2'],
                            ['field' => 'email', 'message' => 'Invalid email address'],
                            ['field' => 'status', 'message' => 'Status not existing'],
                            ['field' => 'user_role', 'message' => 'User role not existing'],
                        ]
                    ]
                ]
            ],
            [
                // Values too long
                'request_body' => [
                    'first_name' => str_repeat('i', 101),
                    'surname' => str_repeat('i', 101),
                    'email' => 'new.email.@test.ch',
                ],
                'json_response' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'message' => 'There is a validation error when trying to update a user',
                        'errors' => [
                            ['field' => 'first_name', 'message' => 'Maximum length is 100'],
                            ['field' => 'surname', 'message' => 'Maximum length is 100'],
                            ['field' => 'email', 'message' => 'Invalid email address'],
                        ]
                    ]
                ]
            ],
        ];
    }
}