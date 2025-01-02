<?php

namespace App\Modules\Authentication\Domain\Exception;

class UserAlreadyVerifiedException extends \RuntimeException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
