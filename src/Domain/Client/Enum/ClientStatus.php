<?php

namespace App\Domain\Client\Enum;

enum ClientStatus: string
{
    case ACTION_PENDING = 'Action pending';
    case IN_CARE = 'In care';
    case HELPED = 'Helped';
    case CANNOT_HELP = 'Cannot help';
}
