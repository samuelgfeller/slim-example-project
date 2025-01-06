<?php

namespace App\Module\Client\Domain\Exception;

class InvalidClientFilterException extends \RuntimeException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
