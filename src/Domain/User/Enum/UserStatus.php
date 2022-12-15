<?php

namespace App\Domain\User\Enum;

use App\Common\Trait\EnumToArray;

/**
 * User authentication status.
 */
enum UserStatus: string
{
    use EnumToArray;

    // First letter uppercase and rest lowercase as names are used as labels in html form
    case Unverified = 'unverified'; // Default after registration
    case Active = 'active'; // Verified via token received in email
    case Locked = 'locked'; // Locked for security reasons, may be reactivated by account holder via email
    case Suspended = 'suspended'; // User suspended, account holder not allowed to login even via email

    // UserStatus::toArray() returns array for dropdown
}
