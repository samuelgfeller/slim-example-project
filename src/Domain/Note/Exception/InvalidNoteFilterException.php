<?php

namespace App\Domain\Note\Exception;

class InvalidNoteFilterException extends \RuntimeException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
