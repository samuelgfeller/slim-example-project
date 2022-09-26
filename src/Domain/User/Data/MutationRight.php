<?php

namespace App\Domain\User\Data;

enum MutationRight: string
{
    case ALL = 'all';
    case NONE = 'none';
}
