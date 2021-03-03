<?php

namespace App\Domain\Exceptions;

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
