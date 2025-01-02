<?php

namespace App\Modules\Authentication\Domain\Exception;

class AuthenticationException extends \RuntimeException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
