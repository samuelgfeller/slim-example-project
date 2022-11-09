<?php

namespace App\Domain\User\Enum;

enum UserStatus: string
{
    // User authentication status
    case STATUS_UNVERIFIED = 'unverified'; // Default after registration
    case STATUS_ACTIVE = 'active'; // Verified via token received in email
    case STATUS_LOCKED = 'locked'; // Locked for security reasons, may be reactivated by account holder via email
    case STATUS_SUSPENDED = 'suspended'; // User suspended, account holder not allowed to login even via email

}
