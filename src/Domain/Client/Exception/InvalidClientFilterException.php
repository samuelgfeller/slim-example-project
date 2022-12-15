<?php

namespace App\Domain\Client\Exception;

/**
 * Class ValidationException.
 */
class InvalidClientFilterException extends \RuntimeException
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
