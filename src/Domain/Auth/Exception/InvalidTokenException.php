<?php

namespace App\Domain\Auth\Exception;

/**
 * Class InvalidTokenException.
 * When token is invalid (e.g. registration)
 */
class InvalidTokenException extends \RuntimeException
{

    public function __construct($message)
    {
        parent::__construct($message);
    }
}