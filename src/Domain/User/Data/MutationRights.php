<?php

namespace App\Domain\User\Data;

enum MutationRights: string
{
    case ALL = 'all';
    case READ = 'read';
    case NONE = 'none';
    case OWN = 'own';
}
