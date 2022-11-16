<?php

namespace App\Domain\User\Enum;

enum UserRole : string
{
    // Value is `user_role`.`name`
    case NEWCOMER = 'newcomer';
    case ADVISOR = 'advisor';
    case MANAGING_ADVISOR = 'managing_advisor';
    case ADMIN = 'admin';
}
