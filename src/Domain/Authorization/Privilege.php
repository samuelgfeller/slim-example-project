<?php

namespace App\Domain\Authorization;

enum Privilege: string
{
    // No rights
    case NONE = 'NONE';
    // Allowed to read all entries
    case READ = 'R';
    // Allowed to read and create all entries
    case CREATE = 'CR';
    // Allowed to read, create and update all entries
    case UPDATE = 'CRU';
    // Allowed to read, create, update and delete all entries
    case DELETE = 'CRUD';
    // Allowed to do everything on each note
    // case ALL = 'ALL';

    /**
     * Check if granted to perform action with given needed rights.
     * Not sure though if it's smart to implement a hierarchical system
     * for CRUD operations or if a collection or permissions would be better.
     *
     * @param Privilege $requiredPrivilege
     * @return bool
     */
    public function hasPrivilege(Privilege $requiredPrivilege): bool
    {
        return match ($requiredPrivilege){
            self::READ => in_array($this, [self::READ, self::CREATE, self::UPDATE, self::DELETE], true),
            self::CREATE => in_array($this, [self::CREATE, self::UPDATE, self::DELETE], true),
            self::UPDATE => in_array($this, [self::UPDATE, self::DELETE], true),
            self::DELETE => $this === self::DELETE,
            self::NONE => true,
        };
    }
}
