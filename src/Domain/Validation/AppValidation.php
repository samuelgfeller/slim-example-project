<?php

namespace App\Domain\Validation;

use App\Domain\Exception\ValidationException;
use Psr\Log\LoggerInterface;

/**
 * Class AppValidation
 */
abstract class AppValidation
{
    protected $logger;

    /**
     * AppValidation constructor.
     * @param LoggerInterface $logger
     */
    protected function __construct(LoggerInterface $logger)
    {
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
            throw new ValidationException($validationResult);
        }
    }
    
    /**
     * Check if a values string is less than a defined value.
     *
     * @param $value
     * @param $fieldname
     * @param ValidationResult $validationResult
     * @param int $length
     */
    protected function validateLengthMin($value, $fieldname, ValidationResult $validationResult, $length = 3): void
    {
        if (strlen(trim($value)) < $length) {
            $validationResult->setError($fieldname, sprintf('Required minimum length is %s', $length));
        }
    }

    /**
     * Check if a values string length is more than a defined value.
     *
     * @param $value
     * @param $fieldname
     * @param ValidationResult $validationResult
     * @param int $length
     */
    protected function validateLengthMax($value, $fieldname, ValidationResult $validationResult, $length = 255): void
    {
        if (strlen(trim($value)) > $length) {
            $validationResult->setError($fieldname, sprintf('Required maximum length is %s', $length));
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
