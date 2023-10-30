<?php

namespace App\Domain\Note\Exception;


class InvalidNoteFilterException extends \RuntimeException
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
