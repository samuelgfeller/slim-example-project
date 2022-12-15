<?php

namespace App\Test\Provider\User;

use App\Domain\User\Enum\UserRole;
use App\Test\Traits\FixtureTestTrait;
use Fig\Http\Message\StatusCodeInterface;

class UserChangePasswordProvider
{
    use FixtureTestTrait;

    /**
     * User update authorization cases
     * Provides combination of different user roles with expected result.
     *
     * @return array[]
     */
    public function userPasswordChangeAuthorizationCases(): array
    {
        // Password hash to verify old password - 12345678 is used in test function
        $passwordHash = password_hash('12345678', PASSWORD_DEFAULT);
        // Set different user role attributes
        $adminAttr = ['user_role_id' => UserRole::ADMIN, 'password_hash' => $passwordHash];
        $managingAdvisorAttr = ['user_role_id' => UserRole::MANAGING_ADVISOR, 'password_hash' => $passwordHash];
        // If one attribute is different they are differentiated and 2 separated users are added to the db
        $otherManagingAdvisorAttr = [
            'first_name' => 'Other',
            'user_role_id' => UserRole::MANAGING_ADVISOR,
            'password_hash' => $passwordHash,
        ];

        $advisorAttr = ['user_role_id' => UserRole::ADVISOR, 'password_hash' => $passwordHash];
        $newcomerAttr = ['user_role_id' => UserRole::NEWCOMER, 'password_hash' => $passwordHash];

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
                'message' => 'Not allowed to change password.',
            ],
        ];

        // To avoid testing each column separately for each user role, the most basic change is taken to test.
        // [foreign_key => 'new'] will be replaced in test function as user has to be added to the database first
        return [
            // * Newcomer
            // "owner" means from the perspective of the authenticated user
            [ // ? Newcomer owner - allowed
                'user_to_change' => $newcomerAttr,
                'authenticated_user' => $newcomerAttr,
                'expected_result' => $authorizedResult,
            ],
            // Higher privilege than newcomer must not be tested as authorization is hierarchical meaning if
            // the lowest privilege is allowed to do action, higher will be able too.
            // * Advisor
            [ // ? Advisor not owner - user to change is newcomer - not allowed
                'user_to_change' => $newcomerAttr,
                'authenticated_user' => $advisorAttr,
                'expected_result' => $unauthorizedResult,
            ],
            // * Managing advisor
            [ // ? Managing advisor not owner - user to change is advisor - allowed
                'user_to_change' => $advisorAttr,
                'authenticated_user' => $managingAdvisorAttr,
                'expected_result' => $authorizedResult,
            ],
            [ // ? Managing advisor not owner - user to change is other managing advisor - not allowed
                'user_to_change' => $otherManagingAdvisorAttr,
                'authenticated_user' => $managingAdvisorAttr,
                'expected_result' => $unauthorizedResult,
            ],
            // * Admin
            [ // ? Admin not owner - user to change is managing advisor - allowed
                'user_to_change' => $managingAdvisorAttr,
                'authenticated_user' => $adminAttr,
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
    public function invalidPasswordChangeCases(): array
    {
        // Including as many values as possible that trigger validation errors in each case
        return [
            [
                // Values too short
                'request_body' => [
                    'password' => '12',
                    'password2' => '1',
                    // Old password not relevant for string validation as it verified that it's the correct one
                ],
                'json_response' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'message' => 'There is a validation error with the passwords.',
                        'errors' => [
                            ['field' => 'password2', 'message' => 'Passwords do not match'],
                            ['field' => 'password', 'message' => 'Minimum length is 3'],
                            ['field' => 'password2', 'message' => 'Minimum length is 3'],
                        ],
                    ],
                ],
            ],
            [
                // Wrong old password
                'request_body' => [
                    'old_password' => 'wrong-old-password',
                    'password' => '12345678',
                    'password2' => '12345678',
                ],
                'json_response' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'message' => 'There is a validation error with the password.',
                        'errors' => [
                            ['field' => 'old_password', 'message' => 'Incorrect password'],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Provide malformed bodies for password change submit request as well as
     * according error messages.
     *
     * @return array[]
     */
    public function malformedPasswordChangeRequestCases(): array
    {
        return [
            [
                // Empty body
                'requestBody' => [],
            ],
            [
                // Body "null" (because both can happen )
                'requestBody' => null,
            ],
            // Missing 'old_password' currently not tested as it's not required when privileged user tries to
            // modify other user
            [
                // Missing  'password'
                'requestBody' => [
                    'old_password' => '',
                    'password2' => '',
                ],
            ],
            [
                // Missing 'password2'
                'requestBody' => [
                    'old_password' => '',
                    'password' => '',
                ],
            ],
        ];
    }
}
