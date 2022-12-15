<?php

namespace App\Domain\Authentication\Exception;

/**
 * When user tries to log in but the status is not active
 * Intentionally using the same exception for different statuses because
 * it is caught and displayed the same to the user (vague divulge only the strict necessary)
 * The full explanation is in the email.
 */
class UnableToLoginStatusNotActiveException extends \RuntimeException
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
