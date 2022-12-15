<?php

namespace App\Domain\Validation;

/**
 * Class ValidationResult.
 */
class ValidationResult
{
    protected string $message;
    protected array $errors = [];
    protected array $validatedData = [];

    /**
     * ValidationResult constructor.
     *
     * @param string $message
     */
    public function __construct(string $message = 'Validation failed.')
    {
        $this->message = $message;
    }

    /**
     * Get message.
     *
     * @return string|null
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * Set message.
     *
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * Set error.
     *
     * @param string $field
     * @param string $message
     */
    public function setError(string $field, string $message): void
    {
        $this->errors[] = [
            'field' => $field,
            'message' => $message,
        ];
    }

    /**
     * Get errors.
     *
     * @return array $errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return array
     */
    public function getValidatedData(): array
    {
        return $this->validatedData;
    }

    /**
     * @param array $validatedData
     */
    public function setValidatedData(array $validatedData): void
    {
        $this->validatedData = $validatedData;
    }

    public function getStatusCode(): int
    {
        // If anytime in the future status code would somehow change
        return 422;
    }

    /**
     * Fail.
     *
     * Check if there are any errors
     *
     * @return bool
     */
    public function fails(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Success.
     *
     * Check if there are not any errors.
     *
     * @return bool
     */
    public function success(): bool
    {
        return empty($this->errors);
    }

    /**
     * Clear.
     *
     * Clear message and errors
     */
    public function clear(): void
    {
        $this->message = '';
        $this->errors = [];
    }

    /**
     * Validation To Array.
     *
     * Get Validation Context as array
     *
     * @return array $result
     */
    public function toArray(): array
    {
        $result = [
            'message' => $this->message,
            'errors' => $this->errors,
        ];

        return $result;
    }
}
