<?php

namespace App\Test\Provider\Authentication;

use App\Domain\User\Enum\UserStatus;
use Fig\Http\Message\StatusCodeInterface;

class AuthenticationProvider
{
    /**
     * Provide status and partial email content for registration test on existing user.
     *
     * @return array
     */
    public function registerExistingUserStatusAndEmailCases(): array
    {
        return [
            [
                'existing_user_status' => UserStatus::Active,
                'partial_email_body' => 'If this was you, then you can login with your credentials by navigating to the',
            ],
            [
                'existing_user_status' => UserStatus::Locked,
                'partial_email_body' => 'If this was you, then we have the regret to inform you that your account is locked for security reasons',
            ],
            [
                'existing_user_status' => UserStatus::Suspended,
                'partial_email_body' => 'If this was you, then we have the regret to inform you that your account is suspended',
            ],
        ];
    }

    /**
     * Provide status and partial email content for login test user that is not active.
     *
     * @return array
     */
    public function nonActiveAuthenticationRequestCases(): array
    {
        return [
            [
                'status' => UserStatus::Unverified,
                'partial_email_body' => 'If you just tried to log in, please take note that you have to validate your email address first.',
            ],
            [
                'status' => UserStatus::Locked,
                'partial_email_body' => 'If you just tried to log in, please take note that your account is locked.',
            ],
            [
                'status' => UserStatus::Suspended,
                'partial_email_body' => 'If you just tried to log in, please take note that your account is suspended.',
            ],
        ];
    }

    /**
     * Provide status and partial email content for login test user that is not active
     * In provider mainly to reset database between correct and incorrect requests.
     *
     * @return array
     */
    public function authenticationSecurityCases(): array
    {
        return [
            [
                'correct_credentials' => true,
                'status_code' => StatusCodeInterface::STATUS_FOUND,
            ],
            // [
            //     'correct_credentials' => false,
            //     'status_code' => StatusCodeInterface::STATUS_UNAUTHORIZED,
            // ],
        ];
    }

    /**
     * Invalid login credentials provider that should fail validation.
     *
     * @return string[][][]
     */
    public function invalidLoginCredentialsProvider(): array
    {
        return [
            [
                [
                    // Invalid email
                    'email' => 'admin@exam$ple.com',
                    'password' => '12345678',
                ],
            ],
            [
                [
                    // Missing email
                    'email' => '',
                    'password' => '12345678',
                ],
            ],
            [
                [
                    // Invalid password
                    'email' => 'admin@example.com',
                    'password' => '12',
                ],
            ],
            [
                [
                    // Missing password
                    'email' => 'admin@example.com',
                    'password' => '',
                ],
            ],
        ];
    }

    /**
     * Provide malformed request bodies for password reset submit request as well as
     * according error messages.
     *
     * @return array[]
     */
    public function malformedPasswordResetRequestBodyProvider(): array
    {
        return [
            [
                // Empty body
                'body' => [],
                'message' => 'Request body malformed.',
            ],
            [
                // Body "null" (because both can happen )
                'body' => null,
                'message' => 'Request body malformed.',
            ],
            [
                // Leaving out 'password'
                'body' => [
                    'password2' => '',
                    'token' => '',
                    'id' => '',
                ],
                'message' => 'Request body malformed.',
            ],
            [
                // Leaving out 'password2'
                'body' => [
                    'password' => '',
                    'token' => '',
                    'id' => '',
                ],
                'message' => 'Request body malformed.',
            ],
            [
                // Leaving out 'token'
                'body' => [
                    'password' => '',
                    'password2' => '',
                    'id' => '',
                ],
                'message' => 'Request body malformed.',
            ],
            [
                // Leaving out 'id'
                'body' => [
                    'password' => '',
                    'password2' => '',
                    'token' => '',
                ],
                'message' => 'Request body malformed.',
            ],
        ];
    }
}
