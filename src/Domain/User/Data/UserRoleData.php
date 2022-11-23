<?php

namespace App\Domain\User\Data;

final class UserRoleData
{
    public int $id;
    public string $name;
    public int $hierarchy = 100; // Default the lowest hierarchy (null < 1 === true)
}
