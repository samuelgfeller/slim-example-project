<?php

namespace App\Domain\Authorization;

enum UserRole : string
{
    case NEWCOMER = 'newcomer';
    case ADVISOR = 'advisor';
    case MANAGING_ADVISOR = 'managing_advisor';
    case ADMIN = 'admin';
}
