<?php

namespace App\Domain\Authentication\Exception;

/**
 * Class ValidationException.
 */
class AuthenticationException extends \RuntimeException
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
