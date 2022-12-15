<?php

namespace App\Domain\Authentication\Exception;

/**
 * Class InvalidTokenException.
 * When token is invalid (e.g. registration).
 */
class UserAlreadyVerifiedException extends \RuntimeException
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
