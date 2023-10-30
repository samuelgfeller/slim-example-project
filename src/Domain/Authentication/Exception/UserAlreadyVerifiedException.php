<?php

namespace App\Domain\Authentication\Exception;


class UserAlreadyVerifiedException extends \RuntimeException
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
