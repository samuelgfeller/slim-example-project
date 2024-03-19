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
    public static function registerExistingUserStatusAndEmailCases(): array
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
    public static function nonActiveAuthenticationRequestCases(): array
    {
        return [
            [
                'status' => UserStatus::Unverified,
                'partialEmailBody' => 'If you just tried to log in, please take note that you have to validate your email address first.',
            ],
            [
                'status' => UserStatus::Locked,
                'partialEmailBody' => 'If you just tried to log in, please take note that your account is locked.',
            ],
            [
                'status' => UserStatus::Suspended,
                'partialEmailBody' => 'If you just tried to log in, please take note that your account is suspended.',
            ],
        ];
    }

    /**
     * Provide status and partial email content for login test user that is not active
     * In provider mainly to reset database between correct and incorrect requests.
     *
     * @return array
     */
    public static function authenticationSecurityCases(): array
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
     */
    public static function invalidLoginCredentialsProvider(): array
    {
        return [
            'Invalid email' => [
                [
                    'email' => 'admin@exam$ple.com',
                    'password' => '12345678',
                ],
                'errorMessage' => 'Invalid email',
            ],
            'Missing email' => [
                [
                    // Missing email
                    'email' => '',
                    'password' => '12345678',
                ],
                'errorMessage' => 'Invalid email',
            ],
            'Missing password' => [
                [
                    'email' => 'admin@example.com',
                    'password' => '',
                ],
                'errorMessage' => 'Invalid password',
            ],
        ];
    }
}
