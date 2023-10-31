<?php

namespace App\Domain\Authentication\Exception;

class AuthenticationException extends \RuntimeException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
