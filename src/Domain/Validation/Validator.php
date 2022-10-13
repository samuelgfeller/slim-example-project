<?php

namespace App\Domain\Validation;

use App\Common\DateTimeImmutable;
use App\Domain\Exceptions\ValidationException;
use App\Domain\Factory\LoggerFactory;
use App\Infrastructure\Validation\ResourceExistenceCheckerRepository;
use Psr\Log\LoggerInterface;

/**
 * User input validator
 */
final class Validator
{
    public LoggerInterface $logger;

    /**
     * AppValidation constructor. Very important that it is public
     * because PostValidation inherits this constructor and can't
     * be instantiated otherwise
     *
     * @param LoggerFactory $logger
     * @param ResourceExistenceCheckerRepository $userExistenceCheckerRepository
     */
    public function __construct(
        private readonly ResourceExistenceCheckerRepository $userExistenceCheckerRepository,
        LoggerFactory $logger,
    ) {
        // Not LoggerFactory since the instance is created in child class. AppValidation is never instantiated
        $this->logger = $logger->addFileHandler('error.log')->createInstance('input-validation');
    }

    /**
     * Throw a validation exception if the validation result fails.
     *
     * @param ValidationResult $validationResult
     * @throws ValidationException
     */
    public function throwOnError(ValidationResult $validationResult): void
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
    public
    function validateLengthMin(
        $value,
        $fieldName,
        ValidationResult $validationResult,
        $length = 3
    ): void {
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
    public
    function validateLengthMax(
        $value,
        $fieldName,
        ValidationResult $validationResult,
        $length = 255
    ): void {
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
    public
    function validateName(
        string $name,
        string $fieldName,
        ValidationResult $validationResult,
        bool $required = false,
    ): void {
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
    public
    function validateEmail(
        string|null $email,
        ValidationResult $validationResult,
        bool $required = false,
    ): void {
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
     * Validate birthdate
     *
     * @param DateTimeImmutable|null $birthdate
     * @param bool $required
     * @param ValidationResult $validationResult
     */
    public
    function validateBirthdate(
        DateTimeImmutable|null $birthdate,
        ValidationResult $validationResult,
        bool $required,
        $birthdateUserInput = null,
    ): void {
        // Email filter will fail if email is empty and if it's optional it shouldn't throw an error
        if (null !== $birthdate) {
            $now = new \DateTimeImmutable('now');
            // $now is not changed with ->sub as its immutable
            $oldestAge = $now->sub(new \DateInterval('P130Y'));
            // If age in the future or older than the oldest age -> invalid
            if ($birthdate->getTimestamp() > $now->getTimestamp() ||
                $birthdate->getTimestamp() < $oldestAge->getTimestamp()) {
                $validationResult->setError('birthdate', 'Invalid birthdate');
            }
            // Validate that date in object is the same as what the user submitted https://stackoverflow.com/a/19271434/9013718
            if ($birthdateUserInput !== null && $birthdate->format('Y-m-d') !== $birthdateUserInput) {
                $validationResult->setError('birthdate', 'Invalid birthdate. Instance not same as input.');
            }
        } elseif (true === $required) {
            // If it is null and required
            $validationResult->setError('birthdate', 'Birthdate required but not given');
        }
    }

    /**
     * Check if user resource
     *
     * @param null|int $rowId
     * @param string $table
     * @param ValidationResult $validationResult
     * @param bool $required
     */
    public function validateExistence(
        ?int $rowId,
        string $table,
        ValidationResult $validationResult,
        bool $required = false,
    ): void {
        if (null !== $rowId && $rowId !== 0) {
            $exists = $this->userExistenceCheckerRepository->rowExists($rowId, $table);
            if (!$exists) {
                $validationResult->setError($table, ucfirst($table) . ' not existing');

                $this->logger->debug("Checked for $table id $rowId but it didn\'t exist in validation");
            }
        } elseif (true === $required) {
            $validationResult->setError($table . '_id', $table . '_id required but not given');
        }
    }

    /**
     * Validate that given input is numeric
     *
     * @param string|int|null $numericValue
     * @param string $fieldName
     * @param bool $required
     * @param ValidationResult $validationResult
     * @return void
     */
    public function validateNumeric(
        string|null|int $numericValue,
        string $fieldName,
        ValidationResult $validationResult,
        bool $required = false,
    ): void {
        if (null !== $numericValue && '' !== $numericValue) {
            if (is_numeric($numericValue) === false) {
                $validationResult->setError($fieldName, 'Value should be numeric but wasn\'t.');
            }
        } elseif (true === $required) {
            // If it is null or empty string and required
            $validationResult->setError($fieldName, 'Field is required but not given');
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
