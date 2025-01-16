<?php

namespace App\Test\TestCase\User\Update;

use App\Module\User\Enum\UserRole;
use Fig\Http\Message\StatusCodeInterface;

class UserUpdateProvider
{
    /**
     * @return array[]
     */
    public static function userUpdateAuthorizationCases(): array
    {
        // Set different user role attributes
        $managingAdvisorAttr = ['user_role_id' => UserRole::MANAGING_ADVISOR];
        $otherManagingAdvisorAttr = ['user_role_id' => UserRole::MANAGING_ADVISOR, 'first_name' => 'George'];
        $advisorAttr = ['user_role_id' => UserRole::ADVISOR];
        $newcomerAttr = ['user_role_id' => UserRole::NEWCOMER];

        $authorizedResult = [
            StatusCodeInterface::class => StatusCodeInterface::STATUS_OK,
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
                'message' => 'Not allowed to update user.',
            ],
        ];
        $basicDataChanges = [
            'first_name' => 'NewFirstName',
            'last_name' => 'NewLastName',
            'email' => 'new.email@test.ch',
            'theme' => 'dark',
            'language' => 'de_CH',
            // Password hash change tested in UserChangePasswordActionTest
        ];

        // To avoid testing each column separately for each user role, the most basic changes are taken to test.
        // [foreign_key => 'new'] will be replaced in test function as user has to be added to the database.
        return [
            // * Newcomer
            // "owner" means from the perspective of the authenticated user
            [ // ? Newcomer owner - basic data change - allowed
                'userToChangeRow' => $newcomerAttr,
                'authenticatedUserRow' => $newcomerAttr,
                // Data to be changed
                'requestData' => $basicDataChanges,
                'expectedResult' => $authorizedResult,
            ],

            // * Advisor
            [ // ? Advisor owner - status change - not allowed
                'userToChangeRow' => $advisorAttr,
                'authenticatedUserRow' => $advisorAttr,
                // Data to be changed
                'requestData' => ['status' => 'active'],
                'expectedResult' => $unauthorizedResult,
            ],
            [ // ? Advisor owner - user role change - not allowed even to newcomer
                'userToChangeRow' => $advisorAttr,
                'authenticatedUserRow' => $advisorAttr,
                // Data to be changed
                'requestData' => ['user_role_id' => UserRole::ADMIN],
                'expectedResult' => $unauthorizedResult,
            ],
            [ // ? Advisor not owner - basic data - not allowed
                'userToChangeRow' => $newcomerAttr,
                'authenticatedUserRow' => $advisorAttr,
                // Data to be changed
                'requestData' => $basicDataChanges,
                'expectedResult' => $unauthorizedResult,
            ],
            // * Managing advisor
            [ // ? Managing advisor not owner - user to change is advisor (to user role advisor) - allowed
                'userToChangeRow' => $advisorAttr,
                'authenticatedUserRow' => $managingAdvisorAttr,
                // Data to be changed
                'requestData' => array_merge(
                    $basicDataChanges,
                    ['user_role_id' => UserRole::ADVISOR, 'status' => 'active']
                ),
                'expectedResult' => $authorizedResult,
            ],
            [ // ? Managing advisor not owner - user to change is advisor (to user role managing advisor) - not allowed
                'userToChangeRow' => $advisorAttr,
                'authenticatedUserRow' => $managingAdvisorAttr,
                // Data to be changed
                'requestData' => ['user_role_id' => UserRole::MANAGING_ADVISOR],
                'expectedResult' => $unauthorizedResult,
            ],
            [ // ? Managing advisor not owner - user to change is managing advisor - not allowed even basic data
                'userToChangeRow' => $otherManagingAdvisorAttr,
                'authenticatedUserRow' => $managingAdvisorAttr,
                // Data to be changed
                'requestData' => $basicDataChanges,
                'expectedResult' => $unauthorizedResult,
            ],
            [ // ? Managing advisor owner - own role to admin - not allowed
                'userToChangeRow' => $managingAdvisorAttr,
                'authenticatedUserRow' => $managingAdvisorAttr,
                // Data to be changed
                'requestData' => ['user_role_id' => UserRole::ADMIN],
                'expectedResult' => $unauthorizedResult,
            ],
        ];
    }

    /**
     * Returns combinations of invalid data to trigger validation exception
     * for modification.
     *
     * @return array
     */
    public static function invalidUserUpdateCases(): array
    {
        // Including as many values as possible that trigger validation errors in each case
        return [
            [
                'requestBody' => [
                    // Values too short
                    'first_name' => 'n',
                    'last_name' => 'n',
                    'email' => 'new.email@tes$t.ch',
                    'status' => 'non-existing',
                    'user_role_id' => 99,
                    'theme' => 'invalid',
                ],
                'jsonResponse' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'errors' => [
                            'first_name' => [0 => 'Minimum length is 2'],
                            'last_name' => [0 => 'Minimum length is 2'],
                            'email' => [0 => 'Invalid email'],
                            'status' => [0 => 'Invalid option'],
                            'user_role_id' => [0 => 'Invalid option'],
                            'theme' => [0 => 'Invalid option'],
                        ],
                    ],
                ],
            ],
            [
                // Values too long
                'requestBody' => [
                    'first_name' => str_repeat('i', 101),
                    'last_name' => str_repeat('i', 101),
                    'email' => 'new.email.@test.ch',
                ],
                'jsonResponse' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'errors' => [
                            'first_name' => [0 => 'Maximum length is 100'],
                            'last_name' => [0 => 'Maximum length is 100'],
                            'email' => [0 => 'Invalid email'],
                        ],
                    ],
                ],
            ],
        ];
    }
}
