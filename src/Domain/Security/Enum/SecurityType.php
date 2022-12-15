<?php

namespace App\Domain\Security\Enum;

enum SecurityType: string
{
    case GLOBAL_LOGIN = 'global_login';
    case GLOBAL_EMAIL = 'global_email';
    case GLOBAL_REQUESTS = 'global_requests';
    case USER_LOGIN = 'user_login'; // User or IP fail
    case USER_EMAIL = 'user_email';
    case USER_REQUESTS = 'user_requests';
}
