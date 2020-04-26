<?php

namespace App\Domain\Exception;

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
