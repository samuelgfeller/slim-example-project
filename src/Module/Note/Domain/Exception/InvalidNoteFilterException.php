<?php

namespace App\Module\Note\Domain\Exception;

class InvalidNoteFilterException extends \RuntimeException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
