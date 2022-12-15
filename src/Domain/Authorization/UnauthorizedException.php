<?php

namespace App\Domain\Authorization;

/**
 * 401 Unauthorized when trying to access page or data without being logged in.
 */
class UnauthorizedException extends \RuntimeException
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
