<?php

namespace App\Domain\Exception;

/**
 * When client makes a request that attempts to do something that is
 * not designed to be possible in the application.
 */
class InvalidOperationException extends \RuntimeException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
