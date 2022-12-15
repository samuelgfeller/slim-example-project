<?php

namespace App\Domain\Note\Exception;

/**
 * Class ValidationException.
 */
class InvalidNoteFilterException extends \RuntimeException
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
