<?php

namespace App\Domain\User\Enum;

enum UserStatus: string
{
    // User authentication status
    case UNVERIFIED = 'unverified'; // Default after registration
    case ACTIVE = 'active'; // Verified via token received in email
    case LOCKED = 'locked'; // Locked for security reasons, may be reactivated by account holder via email
    case SUSPENDED = 'suspended'; // User suspended, account holder not allowed to login even via email

}
