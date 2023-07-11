<?php

namespace App\Domain\Validation;

use App\Domain\Factory\LoggerFactory;
use App\Infrastructure\Validation\ResourceExistenceCheckerRepository;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;

/**
 * User input validator.
 */
final class Validator
{
    public LoggerInterface $logger;

    /**
     * AppValidation constructor. Very important that it is public
     * because PostValidation inherits this constructor and can't
     * be instantiated otherwise.
     *
     * @param LoggerFactory $logger
     * @param ResourceExistenceCheckerRepository $resourceExistenceCheckerRepository
     */
    public function __construct(
        public readonly ResourceExistenceCheckerRepository $resourceExistenceCheckerRepository,
        LoggerFactory $logger,
    ) {
        // Not LoggerFactory since the instance is created in child class. AppValidation is never instantiated
        $this->logger = $logger->addFileHandler('error.log')->createInstance('input-validation');
    }

    /**
     * Throw a validation exception if the validation result fails.
     *
     * @param ValidationResult $validationResult
     *
     * @throws ValidationException|\JsonException
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
     * @param string|int|null $value
     * @param string $fieldName
     * @param ValidationResult $validationResult
     * @param int $length
     */
    public function validateLengthMin(
        string|int|null $value,
        string $fieldName,
        ValidationResult $validationResult,
        int $length = 3
    ): void {
        if (strlen(trim((string)$value)) < $length) {
            $validationResult->setError($fieldName, sprintf(__('Minimum length is %s'), $length));
        }
    }

    /**
     * Check if a values string length is more than a defined value.
     *
     * @param string|int|null $value
     * @param string $fieldName
     * @param ValidationResult $validationResult
     * @param int $length
     */
    public function validateLengthMax(
        string|int|null $value,
        string $fieldName,
        ValidationResult $validationResult,
        int $length = 255
    ): void {
        if (mb_strlen(trim((string)$value)) > $length) {
            $validationResult->setError($fieldName, sprintf(__('Maximum length is %s'), $length));
        }
    }

    /**
     * Validate Name.
     *
     * @param string|null $name
     * @param string $fieldName first_name or surname
     * @param ValidationResult $validationResult
     * @param bool $required on update the name doesn't have to be set but on creation it has
     */
    public function validateName(
        ?string $name,
        string $fieldName,
        ValidationResult $validationResult,
        bool $required = false,
    ): void {
        if ('' !== $name && null !== $name) {
            $this->validateLengthMax($name, $fieldName, $validationResult, 100);
            $this->validateLengthMin($name, $fieldName, $validationResult, 2);
        } // elseif only executed if previous "if" is falsy
        elseif (true === $required) {
            $validationResult->setError($fieldName, __('Required'));
        }
    }

    /**
     * Validate email.
     *
     * @param string|null $email
     * @param bool $required
     * @param ValidationResult $validationResult
     */
    public function validateEmail(
        ?string $email,
        ValidationResult $validationResult,
        bool $required = false,
    ): void {
        // Email filter will fail if email is empty and if it's optional it shouldn't throw an error
        if (null !== $email && '' !== $email) {
            // reversed, if true -> error
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $validationResult->setError('email', __('Invalid value'));
            }
        } elseif (true === $required) {
            // If it is null or empty string and required
            $validationResult->setError('email', __('Required'));
        }
    }

    /**
     * Validate birthdate.
     *
     * @param \DateTimeImmutable|string|null $birthdate
     * @param ValidationResult $validationResult
     * @param bool $required
     * @param string $format
     */
    public function validateBirthdate(
        DateTimeImmutable|string|null $birthdate,
        ValidationResult $validationResult,
        bool $required = false,
        string $format = 'd.m.Y'
    ): void {
        // Validate that date user input is valid data
        if (null !== $birthdate && '' !== $birthdate) {
            // strtotime($birthdate) can't be used for date validation as time values are also correct (e.g. 13.13.1)
            if (is_string($birthdate) === true && !empty($birthdate)) {
                // If birthdate is string, create DateTimeImmutable object for validation
                $birthdateObj = DateTimeImmutable::createFromFormat($format, $birthdate);
                // Allow default format Y-m-d as well (client creation via API submit)
                if (false === $birthdateObj || array_sum($birthdateObj->getLastErrors())) {
                    // Try with default format and original value
                    $birthdateObj = DateTimeImmutable::createFromFormat('Y-m-d', $birthdate);
                }
                $birthdate = $birthdateObj;
            }
            if (!($birthdate instanceof \DateTimeImmutable) || array_sum($birthdate->getLastErrors())) {
                // Birthdate is not null, not a string with valid date and also not an instance of the custom
                // DateTimeImmutable format (from the data object) it means that its invalid
                $validationResult->setError('birthdate', __('Invalid value dd.mm.YYYY'));

                return;
            }

            $now = new \DateTimeImmutable('now');
            // $now is not changed with ->sub as its immutable
            $oldestAge = $now->sub(new \DateInterval('P130Y'));
            // If age in the future or older than the oldest age -> invalid
            if ($birthdate->getTimestamp() > $now->getTimestamp()
                || $birthdate->getTimestamp() < $oldestAge->getTimestamp()) {
                $validationResult->setError('birthdate', __('Invalid value'));
            }
            // Validate that date in object is the same as what the user submitted https://stackoverflow.com/a/19271434/9013718
            // There are cases where client submits data in different format than Y-m-d so this check was removed
            // if ($birthdateUserInput !== null && $birthdate->format('Y-m-d') !== $birthdateUserInput) {
            //     $validationResult->setError('birthdate', 'Invalid birthdate. Instance not same as input');
            // }
        } elseif (true === $required) {
            // If it is null and required
            $validationResult->setError('birthdate', __('Required'));
        }
    }

    /**
     * Check if user resource.
     *
     * @param int|null $rowId
     * @param string $table
     * @param ValidationResult $validationResult
     * @param bool $required
     * @param bool $excludingSoftDelete
     */
    public function validateExistence(
        ?int $rowId,
        string $table,
        ValidationResult $validationResult,
        bool $required = false,
        bool $excludingSoftDelete = true
    ): void {
        if (null !== $rowId && $rowId !== 0) {
            $exists = $this->resourceExistenceCheckerRepository->rowExists(
                ['id' => $rowId],
                $table,
                $excludingSoftDelete
            );
            if (!$exists) {
                $validationResult->setError(
                    $table . '_id',
                    __('Invalid option')
                );

                $this->logger->debug("Checked for $table id $rowId but it didn\'t exist in validation");
            }
        } elseif (true === $required) {
            $validationResult->setError($table . '_id', __('Required'));
        }
    }

    /**
     * Validate that given input is numeric.
     *
     * @param string|int|null $numericValue
     * @param string $fieldName
     * @param bool $required
     * @param ValidationResult $validationResult
     *
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
                $validationResult->setError($fieldName, __('Value should be numeric.'));
            }
        } elseif (true === $required) {
            // If it is null or empty string and required
            $validationResult->setError($fieldName, __('Required'));
        }
    }

    /**
     * Validate user status dropdown.
     *
     * @template Enum
     *
     * @param \BackedEnum|string|null $value enum case or backed string value
     * @param class-string $enum e.g. UserRole::class
     * @param string $fieldName
     * @param ValidationResult $validationResult
     * @param bool $required
     *
     * @return void
     */
    public function validateBackedEnum(
        \BackedEnum|string|null $value,
        string $enum,
        string $fieldName,
        ValidationResult $validationResult,
        bool $required = false
    ): void {
        if (null !== $value && '' !== $value) {
            // If $value is already an enum case, it means that its valid
            if (!is_a($value, $enum, true) && !is_a($enum::tryFrom($value), $enum, true)) {
                $validationResult->setError($fieldName, __('Invalid option'));
            }
            // Check if given user status is one of the enum cases
            // if (!in_array($value, $enum::values(), true)) {
            // }
        } elseif (true === $required) {
            // If it is null or empty string and required
            $validationResult->setError($fieldName, __('Required'));
        }
    }
}
