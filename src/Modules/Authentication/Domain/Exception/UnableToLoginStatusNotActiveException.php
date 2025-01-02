<?php

namespace App\Modules\Authentication\Domain\Exception;

/**
 * When the user tries to log in but the status is not active.
 * Intentionally using the same exception for different statuses because
 * it is caught and displayed the same to the user (vague to divulge only the strict necessary information).
 * The full explanation is in the email the user receives.
 */
class UnableToLoginStatusNotActiveException extends \RuntimeException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
