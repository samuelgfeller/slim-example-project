<?php

namespace App\Domain\Validation;

use RuntimeException;

/**
 * Class ValidationException.
 */
class ValidationException extends RuntimeException
{
    /**
     * ValidationException constructor.
     *
     * @param ValidationResult $validationResult
     */
    public function __construct(private readonly ValidationResult $validationResult)
    {
        parent::__construct($validationResult->getMessage());
    }

    /**
     * Get the validation result.
     *
     * @return ValidationResult
     */
    public function getValidationResult(): ValidationResult
    {
        return $this->validationResult;
    }
}
