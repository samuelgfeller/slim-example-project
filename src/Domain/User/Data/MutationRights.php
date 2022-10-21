<?php

namespace App\Domain\User\Data;

enum MutationRights: string
{
    // No rights
    case NONE = 'none';
    // Allowed to read all entries
    case READ = 'read';
    // Allowed to read and create all entries
    case CREATE = 'create';
    // Allowed to read, create and update all entries
    case UPDATE = 'update';
    // Allowed to read, create, update and delete all entries
    case DELETE = 'delete';
    // Allowed to do everything on each note
    case ALL = 'all';
}
