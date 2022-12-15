<?php

namespace App\Domain\Client\Exception;

/**
 * Action not allowed.
 */
class NotAllowedException extends \RuntimeException
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
