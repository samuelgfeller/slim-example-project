<?php

namespace App\Module\Authentication\TokenVerification\Exception;

use App\Module\User\Data\UserData;

class InvalidTokenException extends \RuntimeException
{
    public UserData $userData;

    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
