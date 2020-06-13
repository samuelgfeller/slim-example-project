<?php

namespace App\Domain\Exceptions;

use RuntimeException;

/**
 * Class ValidationException.
 */
class AuthenticationException extends RuntimeException
{

    public function __construct($message)
    {
        parent::__construct($message);
    }
}
