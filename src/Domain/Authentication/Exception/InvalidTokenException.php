<?php

namespace App\Domain\Authentication\Exception;

use App\Domain\User\Data\UserData;


class InvalidTokenException extends \RuntimeException
{
    public UserData $userData;

    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
