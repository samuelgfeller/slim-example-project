<?php

namespace App\Domain\User\Data;

enum MutationRights: string
{
    case ALL = 'all';
    case NONE = 'none';
}
