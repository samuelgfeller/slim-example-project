<?php

namespace App\Domain\Authentication\Exception;

use App\Domain\User\Data\UserData;

/**
 * Class InvalidTokenException.
 * When token is invalid (e.g. registration).
 */
class InvalidTokenException extends \RuntimeException
{
    public UserData $userData;

    public function __construct($message)
    {
        parent::__construct($message);
    }
}
