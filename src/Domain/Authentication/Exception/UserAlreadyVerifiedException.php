<?php

namespace App\Domain\Authentication\Exception;

class UserAlreadyVerifiedException extends \RuntimeException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
