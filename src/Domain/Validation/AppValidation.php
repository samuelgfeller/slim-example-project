<?php

namespace App\Domain\Validation;

use App\Domain\Exceptions\ValidationException;
use Psr\Log\LoggerInterface;

/**
 * Class AppValidation
 */
abstract class AppValidation
{
    protected LoggerInterface $logger;

    /**
     * AppValidation constructor. Very important that it is public
     * because PostValidation inherits this constructor and can't
     * be instantiated otherwise
     *
     * @param LoggerInterface $logger instance created via factory in child classes
     * (like UserValidator.php)
     */
    public function __construct(LoggerInterface $logger)
    {
        // Not LoggerFactory since the instance is created in child class. AppValidation is never instantiated
        $this->logger = $logger;
    }

    /**
     * Throw a validation exception if the validation result fails.
     *
     * @param ValidationResult $validationResult
     * @throws ValidationException
     */
    protected function throwOnError(ValidationResult $validationResult): void
    {
        if ($validationResult->fails()) {
            $this->logger->notice(
                'Validation failed: ' . $validationResult->getMessage() . "\n" . json_encode(
                    $validationResult->getErrors(),
                    JSON_THROW_ON_ERROR
                )
            );
            throw new ValidationException($validationResult);
        }
    }

    /**
     * Check if a values string is less than a defined value.
     *
     * @param $value
     * @param $fieldName
     * @param ValidationResult $validationResult
     * @param int $length
     */
    protected function validateLengthMin($value, $fieldName, ValidationResult $validationResult, $length = 3): void
    {
        if (strlen(trim($value)) < $length) {
            $validationResult->setError($fieldName, sprintf('Required minimum length is %s', $length));
        }
    }

    /**
     * Check if a values string length is more than a defined value.
     *
     * @param $value
     * @param $fieldName
     * @param ValidationResult $validationResult
     * @param int $length
     */
    protected function validateLengthMax($value, $fieldName, ValidationResult $validationResult, $length = 255): void
    {
        if (strlen(trim($value)) > $length) {
            $validationResult->setError($fieldName, sprintf('Required maximum length is %s', $length));
        }
    }

    /**
     * Validate Name.
     *
     * @param string $name
     * @param string $fieldName first_name or surname
     * @param bool $required on update the name doesn't have to be set but on creation it has
     * @param ValidationResult $validationResult
     */
    protected function validateName(string $name, string $fieldName, bool $required, ValidationResult $validationResult): void
    {
        if ('' !== $name) {
            $this->validateLengthMax($name, $fieldName, $validationResult, 100);
            $this->validateLengthMin($name, $fieldName, $validationResult, 2);
        } // elseif only executed if previous "if" is falsy
        elseif (true === $required) {
            $validationResult->setError($fieldName, 'Name required but not given');
        }
    }

    /**
     * Validate email
     *
     * @param string|null $email
     * @param bool $required
     * @param ValidationResult $validationResult
     */
    protected function validateEmail(string|null $email, bool $required, ValidationResult $validationResult): void
    {
        // Email filter will fail if email is empty and if it's optional it shouldn't throw an error
        if (null !== $email && '' !== $email) {
            // reversed, if true -> error
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $validationResult->setError('email', 'Invalid email address');
            }
        } elseif (true === $required && (null === $email || '' === $email)) {
            // If it is null or empty string and required
            $validationResult->setError('email', 'Email required but not given');
        }
    }

    /**
     * Validate the users permission level.
     *
     * @param string $userId
     * @param string $requiredPermissionLevel
     * @param ValidationResult $validationResult
     */
    /*    protected function validatePermissionLevel(string $userId, string $requiredPermissionLevel, ValidationResult $validationResult)
        {
            if ($this->hasPermissionLevel($userId, $requiredPermissionLevel)) {
                $validationResult->setError('permission', __('You do not have the permission to execute this action'));
            }
        }*/

    /**
     * Check if the user has the right permission level.
     *
     * @param string $userId
     * @param string $requiredPermissionLevel
     * @return bool
     */
    /*    protected function hasPermissionLevel(string $userId, string $requiredPermissionLevel)
        {
            $level = $this->userRepository->getUserPermissionLevel($userId);
            return $level >= $requiredPermissionLevel;
        }*/
}
