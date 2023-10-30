<?php

namespace App\Domain\Authentication\Exception;

class AuthenticationException extends \RuntimeException
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
