<?php

namespace App\Domain\User\Data;

use App\Domain\Authorization\Privilege;

class UserResultData extends UserData
{
    // If authenticated user is allowed to change status
    public ?Privilege $statusPrivilege = null;

    // If authenticated user is allowed to change role
    public ?Privilege $userRolePrivilege = null;

    // Authorization limits which entries are in the user role dropdown
    public array $availableUserRoles = [];

}