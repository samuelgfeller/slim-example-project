<?php

namespace App\Domain\User\Enum;

use App\Common\Trait\EnumToArray;

/**
 * User authentication status.
 */
enum UserStatus: string
{
    use EnumToArray;

    // First letter uppercase and rest lowercase because these names are used as labels in html form as they are
    case Unverified = 'unverified'; // Default after registration
    case Active = 'active'; // Verified via token received in email
    case Locked = 'locked'; // Locked for security reasons, may be reactivated by account holder via email
    case Suspended = 'suspended'; // User suspended, account holder not allowed to login even via email

    // UserStatus::toArray() returns array for dropdown

    /**
     * Each enum case has to be in the function below with
     * called by the translation function __() so that poedit
     * recognizes the strings to translate.
     *
     * @return array
     */
    public static function getTranslatedValues(): array
    {
        return [
            __('Unverified'),
            __('Active'),
            __('Locked'),
            __('Suspended'),
        ];
    }
}
