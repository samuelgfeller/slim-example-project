<?php

namespace App\Domain\Authorization;

enum Privilege
{
    // The case names and values correspond to the following privileges:
    // R: Read, C: Create, U: Update, D: Delete
    // They can be combined or used individually depending on the needs of the application.
    // To check if a privilege is allowed, the frontend can check if the letter of the privilege is in the value.
    // For instance, if update privilege is required, the client can check if privilege value contains "U".

    // No rights
    case N;
    // Allowed to Read
    case R;
    // Allowed to Create and Read
    case CR;
    // Allowed only to Create (needed when the user is not allowed to see hidden notes but may create some)
    case C;
    // Allowed to Read, Create and Update
    case CRU;
    // Allowed to Read, Create, Update and Delete
    case CRUD;

    // Initially, the Privilege Enum was the datatype in result objects that was passed to the PHP templates.
    // The case names were the name of the highest privilege (Read, Create, Update, Delete).
    // The values were the letters of the associated permissions meaning Delete was 'CRUD', Update was 'CRU' and so on.
    // This was needed for data returned via Ajax.
    // The frontend could then check if the privilege value contained the letter of the required privilege and
    // the PHP templates called `hasPrivilege()` on the Privilege Enum with the required privilege as argument.
    // This meant that there were 2 different ways to check the same thing.
    // Simplicity is key, so the privilege value that goes to the frontend is now a string even for PHP templates
    // and the names of the Enum cases simply the letters with the permissions.
}
