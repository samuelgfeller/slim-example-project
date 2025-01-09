<?php

namespace App\Module\Authentication\Data;

use App\Module\User\Enum\UserStatus;

class AuthUserData
{
    public ?int $id; // Mysql always returns string from db https://stackoverflow.com/a/5323169/9013718

    public ?string $email;

    public ?string $passwordHash;

    public ?UserStatus $status = null;

}
