<?php

namespace App\Domain\Validation;

use RuntimeException;

/**
 * Class ValidationException.
 */
class ValidationException extends RuntimeException
{
    public array $validationErrors = [];

    public function __construct(array $validationErrors, string $message = 'Validation error')
    {
        parent::__construct($message);

        $this->validationErrors = $validationErrors;
    }
}
