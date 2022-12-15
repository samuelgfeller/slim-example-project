<?php

namespace App\Domain\Authorization;

enum Privilege: string
{
    // ? Instead of the function hasPrivilege() privilege loaded via Ajax by the client checks if the letter is contained
    // in the name. For instance if update privilege is required, the client will check if privilege contains "U".
    // No rights
    case NONE = 'NONE';
    // Allowed to read all entries
    case READ = 'R';
    // Allowed to read and create all entries
    case CREATE = 'CR';
    // Allowed only to create (needed when user cannot see hidden note but may create one)
    case ONLY_CREATE = 'C';
    // Allowed to read, create and update all entries
    case UPDATE = 'CRU';
    // Allowed to read, create, update and delete all entries
    case DELETE = 'CRUD';
    // Allowed to do everything on each note
    // case ALL = 'ALL';

    /**
     * Check if granted to perform action with given needed rights.
     * Not sure though if it's smart to implement a hierarchical system
     * for CRUD operations or if a collection of privileges would be better.
     *
     * @param Privilege $requiredPrivilege
     *
     * @return bool
     */
    public function hasPrivilege(Privilege $requiredPrivilege): bool
    {
        return match ($requiredPrivilege) {
            // Privilege READ is true if $this is either READ, CREATE, UPDATE or DELETE
            self::READ => in_array($this, [self::READ, self::CREATE, self::UPDATE, self::DELETE], true),
            self::CREATE => in_array($this, [self::CREATE, self::ONLY_CREATE, self::UPDATE, self::DELETE], true),
            self::ONLY_CREATE => in_array($this, [self::CREATE, self::ONLY_CREATE], true), // should not be used
            self::UPDATE => in_array($this, [self::UPDATE, self::DELETE], true),
            self::DELETE => $this === self::DELETE,
            self::NONE => true,
        };
    }
}
