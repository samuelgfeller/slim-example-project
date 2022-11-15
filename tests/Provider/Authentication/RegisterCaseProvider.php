<?php

namespace App\Test\Provider\Authentication;


use App\Domain\User\Enum\UserStatus;

class RegisterCaseProvider
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

}