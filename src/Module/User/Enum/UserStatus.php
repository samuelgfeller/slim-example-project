<?php

namespace App\Module\User\Enum;

use App\Core\Shared\Trait\EnumToArray;

/**
 * User status.
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
     * Returns the enum case name that can be displayed by the frontend.
     *
     * @return string
     */
    public function getDisplayName(): string
    {
        return match ($this) {
            self::Unverified => __('Unverified'),
            self::Active => __('Active'),
            self::Locked => __('Locked'),
            self::Suspended => __('Suspended'),
        };
    }
}
