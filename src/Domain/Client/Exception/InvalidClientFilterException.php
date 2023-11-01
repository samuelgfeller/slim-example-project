<?php

namespace App\Domain\Client\Exception;

class InvalidClientFilterException extends \RuntimeException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
