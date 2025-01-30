<?php

namespace App\Test\TestCase\Authentication\PasswordChange;

use App\Module\User\Enum\UserRole;
use Fig\Http\Message\StatusCodeInterface;

class UserChangePasswordProvider
{
    /**
     * User update authorization cases
     * Provides combination of different user roles with expected result.
     *
     * @return array[]
     */
    public static function userPasswordChangeAuthorizationCases(): array
    {
        // Password hash to verify old password - 12345678 is used in test function
        $passwordHash = password_hash('12345678', PASSWORD_DEFAULT);
        // Set different user role attributes
        $adminAttr = ['user_role_id' => UserRole::ADMIN, 'password_hash' => $passwordHash];
        $managingAdvisorAttr = ['user_role_id' => UserRole::MANAGING_ADVISOR, 'password_hash' => $passwordHash];
        // If one attribute is different, they are differentiated and 2 separated users are added to the db
        $otherManagingAdvisorAttr = [
            'first_name' => 'Other',
            'user_role_id' => UserRole::MANAGING_ADVISOR,
            'password_hash' => $passwordHash,
        ];

        $advisorAttr = ['user_role_id' => UserRole::ADVISOR, 'password_hash' => $passwordHash];
        $newcomerAttr = ['user_role_id' => UserRole::NEWCOMER, 'password_hash' => $passwordHash];

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
                'message' => 'Not allowed to change password.',
            ],
        ];

        // To avoid testing each column separately for each user role, the most basic change is taken to test.
        // [foreign_key => 'new'] will be replaced in test function as user has to be added to the database first
        return [
            // * Newcomer
            // "owner" means from the perspective of the authenticated user
            [ // ? Newcomer owner - allowed
                'userToUpdateRow' => $newcomerAttr,
                'authenticatedUserRow' => $newcomerAttr,
                'expectedResult' => $authorizedResult,
            ],
            // Higher privilege than newcomer must not be tested as authorization is hierarchical meaning if
            // the lowest privilege is allowed to do action, higher will be able too.
            // * Advisor
            [ // ? Advisor not owner - user to change is newcomer - not allowed
                'userToUpdateRow' => $newcomerAttr,
                'authenticatedUserRow' => $advisorAttr,
                'expectedResult' => $unauthorizedResult,
            ],
            // * Managing advisor
            [ // ? Managing advisor not owner - user to change is advisor - allowed
                'userToUpdateRow' => $advisorAttr,
                'authenticatedUserRow' => $managingAdvisorAttr,
                'expectedResult' => $authorizedResult,
            ],
            [ // ? Managing advisor not owner - user to change is other managing advisor - not allowed
                'userToUpdateRow' => $otherManagingAdvisorAttr,
                'authenticatedUserRow' => $managingAdvisorAttr,
                'expectedResult' => $unauthorizedResult,
            ],
            // * Admin
            [ // ? Admin not owner - user to change is managing advisor - allowed
                'userToUpdateRow' => $managingAdvisorAttr,
                'authenticatedUserRow' => $adminAttr,
                'expectedResult' => $authorizedResult,
            ],
        ];
    }

    /**
     * Returns combinations of invalid data to trigger validation exception
     * for modification.
     *
     * @return array
     */
    public static function invalidPasswordChangeCases(): array
    {
        // Including as many values as possible that trigger validation errors in each case
        return [
            [
                // Values too short
                'requestBody' => [
                    'password' => '12',
                    'password2' => '1',
                    // Old password not relevant for string validation as it verified that it's the correct one
                ],
                'jsonResponse' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'errors' => [
                            'password' => [0 => 'Minimum length is 3'],
                            'password2' => [0 => 'Minimum length is 3', 1 => 'Passwords do not match'],
                        ],
                    ],
                ],
            ],
            [
                // Wrong old password
                'requestBody' => [
                    'old_password' => 'wrong-old-password',
                    'password' => '12345678',
                    'password2' => '12345678',
                ],
                'jsonResponse' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'errors' => [
                            'old_password' => [0 => 'Incorrect password'],
                        ],
                    ],
                ],
            ],
            [
                // Test with empty request body
                'requestBody' => [],
                'jsonResponse' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'errors' => [
                            'password' => [0 => 'Field is required'],
                            'password2' => [0 => 'Field is required'],
                        ],
                    ],
                ],
            ],
        ];
    }
}
