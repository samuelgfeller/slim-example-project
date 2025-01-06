<?php

namespace App\Module\Authorization\Exception;

/**
 * Class ForbiddenException when user tries to access forbidden area or function.
 */
class ForbiddenException extends \RuntimeException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
