<?php

namespace App\Domain\Authorization;

enum Privilege: string
{
    // The case values are the first letter of the privilege name.
    // These are returned on a JSON Ajax request and used by the frontend to determine if the user has
    // a certain privilege because JS doesn't have access to the hasPrivilege() function.
    // For instance, if update privilege is required, the client will check if privilege value contains "U".

    // No rights
    case NONE = 'NONE';
    // Allowed to read
    case READ = 'R';
    // Allowed to read and create
    case CREATE = 'CR';
    // Allowed only to create (needed when the user is not allowed to see hidden notes but may create some)
    case ONLY_CREATE = 'C';
    // Allowed to read, create and update
    case UPDATE = 'CRU';
    // Allowed to read, create, update and delete
    case DELETE = 'CRUD';
    // Allowed to do everything on each note
    // case ALL = 'ALL';

    /**
     * Checks if the current privilege allows for the required privilege.
     *
     * This method uses a match expression to check if the current privilege ($this) allows for the required privilege.
     * The match expression checks the required privilege against each possible privilege case.
     * For each case, it checks if the current privilege is in an array of privileges that allow for the required privilege.
     * If the current privilege is in the array, the method returns true, indicating that the required privilege is allowed.
     * If the current privilege is not in the array, the method continues to the next case.
     * If no cases match the required privilege, the method returns false,
     * indicating that the required privilege is not allowed.
     *
     * @param Privilege $requiredPrivilege The required privilege.
     * @return bool True if the current privilege allows for the required privilege, false otherwise.
     */
    public function hasPrivilege(Privilege $requiredPrivilege): bool
    {
        return match ($requiredPrivilege) {
            // Privilege READ is true if $this is either READ, CREATE, UPDATE or DELETE
            self::READ => in_array($this, [self::READ, self::CREATE, self::UPDATE, self::DELETE], true),
            self::CREATE => in_array($this, [self::CREATE, self::ONLY_CREATE, self::UPDATE, self::DELETE], true),
            self::ONLY_CREATE => in_array($this, [self::CREATE, self::ONLY_CREATE], true),
            self::UPDATE => in_array($this, [self::UPDATE, self::DELETE], true),
            self::DELETE => $this === self::DELETE,
            self::NONE => true,
        };
    }
}
