<?php

namespace App\Test\Provider\User;

use App\Domain\User\Enum\UserRole;
use App\Test\Traits\FixtureTestTrait;
use Fig\Http\Message\StatusCodeInterface;

class UserUpdateProvider
{
    use FixtureTestTrait;

    /**
     * @return array[]
     */
    public function userUpdateAuthorizationCases(): array
    {
        // Set different user role attributes
        $managingAdvisorAttr = ['user_role_id' => UserRole::MANAGING_ADVISOR];
        $otherManagingAdvisorAttr = ['user_role_id' => UserRole::MANAGING_ADVISOR, 'first_name' => 'George'];
        $advisorAttr = ['user_role_id' => UserRole::ADVISOR];
        $newcomerAttr = ['user_role_id' => UserRole::NEWCOMER];

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
                'message' => 'Not allowed to update user.',
            ],
        ];
        $basicDataChanges = [
            'first_name' => 'NewFirstName',
            'surname' => 'NewLastName',
            'email' => 'new.email@test.ch',
            // Password hash change tested in UserChangePasswordActionTest
        ];
        // To avoid testing each column separately for each user role, the most basic change is taken to test
        // [foreign_key => 'new'] will be replaced in test function as user has to be added to the database
        return [
            // * Newcomer
            // "owner" means from the perspective of the authenticated user
            [ // ? Newcomer owner - basic data change - allowed
                'user_to_change' => $newcomerAttr,
                'authenticated_user' => $newcomerAttr,
                'data_to_be_changed' => $basicDataChanges,
                'expected_result' => $authorizedResult,
            ],

            // * Advisor
            [ // ? Advisor owner - status change - not allowed
                'user_to_change' => $advisorAttr,
                'authenticated_user' => $advisorAttr,
                'data_to_be_changed' => ['status' => 'active'],
                'expected_result' => $unauthorizedResult,
            ],
            [ // ? Advisor owner - user role change - not allowed even to newcomer
                'user_to_change' => $advisorAttr,
                'authenticated_user' => $advisorAttr,
                'data_to_be_changed' => ['user_role_id' => UserRole::ADMIN],
                'expected_result' => $unauthorizedResult,
            ],
            [ // ? Advisor not owner - basic data - not allowed
                'user_to_change' => $newcomerAttr,
                'authenticated_user' => $advisorAttr,
                'data_to_be_changed' => $basicDataChanges,
                'expected_result' => $unauthorizedResult,
            ],
            // * Managing advisor
            [ // ? Managing advisor not owner - user to change is advisor (to user role advisor) - allowed
                'user_to_change' => $advisorAttr,
                'authenticated_user' => $managingAdvisorAttr,
                'data_to_be_changed' => array_merge(
                    $basicDataChanges,
                    ['user_role_id' => UserRole::ADVISOR, 'status' => 'active']
                ),
                'expected_result' => $authorizedResult,
            ],
            [ // ? Managing advisor not owner - user to change is advisor (to user role managing advisor) - not allowed
                'user_to_change' => $advisorAttr,
                'authenticated_user' => $managingAdvisorAttr,
                'data_to_be_changed' => ['user_role_id' => UserRole::MANAGING_ADVISOR],
                'expected_result' => $unauthorizedResult,
            ],
            [ // ? Managing advisor not owner - user to change is managing advisor - not allowed even basic data
                'user_to_change' => $otherManagingAdvisorAttr,
                'authenticated_user' => $managingAdvisorAttr,
                'data_to_be_changed' => $basicDataChanges,
                'expected_result' => $unauthorizedResult,
            ],
            [ // ? Managing advisor owner - own role to admin - not allowed
                'user_to_change' => $managingAdvisorAttr,
                'authenticated_user' => $managingAdvisorAttr,
                'data_to_be_changed' => ['user_role_id' => UserRole::ADMIN],
                'expected_result' => $unauthorizedResult,
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
                        ],
                    ],
                ],
            ],
        ];
    }
}
