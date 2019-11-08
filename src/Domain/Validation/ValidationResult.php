<?php

/**
 * Created by Björn Pfoster for roast.li
 */

namespace App\Domain\Validation;

/**
 * Class ValidationResult.
 */
class ValidationResult
{
    protected $message;
    protected $errors = [];

    /**
     * ValidationResult constructor.
     *
     * @param string $message
     */
    public function __construct(string $message = 'Please check your data')
    {
        $this->message = $message;
    }

    /**
     * Get message.
     *
     * @return null|string
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
        $this->message = null;
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
