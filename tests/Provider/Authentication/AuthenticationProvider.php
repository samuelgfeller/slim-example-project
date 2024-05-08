<?php

namespace App\Test\Provider\Authentication;

use App\Domain\User\Enum\UserStatus;

class AuthenticationProvider
{
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
