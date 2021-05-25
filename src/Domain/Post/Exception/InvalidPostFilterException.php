<?php

namespace App\Domain\Post\Exception;

/**
 * Class ValidationException.
 */
class InvalidPostFilterException extends \RuntimeException
{

    public function __construct($message)
    {
        parent::__construct($message);
    }
}
