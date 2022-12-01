<?php

namespace App\Test\Provider\Authentication;


use App\Domain\User\Enum\UserStatus;

class AuthenticationCaseProvider
{
    /**
     * Provide status and partial email content for registration test on existing user
     *
     * @return array
     */
    public function provideExistingUserStatusAndEmail(): array
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
     * Provide status and partial email content for login test user that is not active
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


}