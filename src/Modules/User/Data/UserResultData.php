<?php

namespace App\Modules\User\Data;

class UserResultData extends UserData
{
    public ?string $generalPrivilege = null;
    // If the authenticated user is allowed to change status
    public ?string $statusPrivilege = null;
    // If the authenticated user is allowed to change the password without having to type in the old password
    public ?string $passwordWithoutVerificationPrivilege = null;

    // If the authenticated user is allowed to change role
    public ?string $userRolePrivilege = null;

    // Authorization limits which entries are in the user role dropdown
    public array $availableUserRoles = [];

    /**
     * Not all object attributes should be passed to view.
     * This function like the toArrayForDatabase returns only
     * the relevant attributes.
     * Private attributes could also be used as they are not
     * serialized by json_encode, but it's a harder to hydrate a
     * collection of results.
     * It has also an added benefit of exactly controlling what and how its serialized.
     */
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'statusPrivilege' => $this->statusPrivilege,
            'userRolePrivilege' => $this->userRolePrivilege,
            'availableUserRoles' => $this->availableUserRoles,
        ]);
    }
}
