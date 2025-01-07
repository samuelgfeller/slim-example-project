<?php

namespace App\Module\Authentication\Exception;

// Used in AccountUnlock feature and RegisterVerification
class UserAlreadyVerifiedException extends \RuntimeException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
