<?php

namespace App\Domain\Note\Exception;

use Slim\Exception\HttpBadRequestException;

/**
 * Class ValidationException.
 */
class InvalidNoteFilterException extends HttpBadRequestException
{

    public function __construct($message)
    {
        parent::__construct($message);
    }
}
