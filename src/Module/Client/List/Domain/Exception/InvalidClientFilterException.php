<?php

namespace App\Module\Client\List\Domain\Exception;

class InvalidClientFilterException extends \RuntimeException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
