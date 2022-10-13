<?php

namespace App\Domain\Client\Service;

use App\Domain\Client\Data\ClientData;
use App\Domain\Exceptions\ValidationException;
use App\Domain\Factory\LoggerFactory;
use App\Domain\Validation\Validator;
use App\Domain\Validation\ValidationResult;

/**
 * Client user input validator
 */
class ClientValidator
{

    /**
     * PostValidator constructor.
     *
     * @param LoggerFactory $logger
     * @param Validator $validator
     */
    public function __construct(
        LoggerFactory $logger,
        private readonly Validator $validator,
    ) {
        $logger->addFileHandler('error.log')->createInstance('post-validation');
    }

    /**
     * Validate post creation
     *
     * @param ClientData $client
     * @throws ValidationException
     */
    public function validateClientCreation(ClientData $client, null|string $birthdateValue = null): void
    {
        // Exact validation error tested in ClientCreateActionTest
        $validationResult = new ValidationResult('There is something in the client data that couldn\'t be validated');

        $this->validateClientStatusId($client->clientStatusId, $validationResult, true);

        $this->validateUserId($client->userId, $validationResult, true);

        // First and last name not required in case advisor only knows either first or last name
        $this->validator->validateName($client->firstName, 'first_name', $validationResult, false);
        $this->validator->validateName($client->lastName, 'last_name', $validationResult, false);

        $this->validator->validateEmail($client->email, $validationResult, false);
        // With birthdate original user input value as it's transformed into a DateTimeImmutable when object gets populated
        $this->validator->validateBirthdate($client->birthdate, $validationResult, false, $birthdateValue);

        $this->validateLocation($client->location, $validationResult, false);

        $this->validatePhone($client->phone, $validationResult, false);

        $this->validateSex($client->sex, $validationResult, false);

        $this->validateClientMessage($client->clientMessage, $validationResult, false);

        $this->validator->throwOnError($validationResult);
    }

    /**
     * Validate post update
     *
     * @param ClientData $client
     * @param string|null $birthdateValue original value from request to validate the date
     */
    public function validateClientUpdate(ClientData $client, null|string $birthdateValue = null): void
    {
        // Exact validation error tested in PostCaseProvider.php::providePostCreateInvalidData()
        $validationResult = new ValidationResult('There is something in the client data that couldn\'t be validated');

        if ($client->clientStatusId !== null) {
            $this->validateClientStatusId($client->clientStatusId, $validationResult, false);
        }
        if ($client->userId !== null) {
            $this->validateUserId($client->userId, $validationResult, false);
        }
        if ($client->firstName !== null) {
            $this->validator->validateName($client->firstName, 'first_name', $validationResult, false);
        }
        if ($client->lastName !== null) {
            $this->validator->validateName($client->lastName, 'last_name', $validationResult, false);
        }
        if ($client->email !== null) {
            $this->validator->validateEmail($client->email, $validationResult, false);
        }
        if ($client->birthdate !== null) {
            $this->validator->validateBirthdate($client->birthdate, $validationResult, false, $birthdateValue);
        }
        if ($client->location !== null) {
            $this->validateLocation($client->location, $validationResult, false);
        }
        if ($client->phone !== null) {
            $this->validatePhone($client->phone, $validationResult, false);
        }
        if ($client->sex !== null) {
            $this->validateSex($client->sex, $validationResult, false);
        }

        $this->validator->throwOnError($validationResult);
    }


    // Validate functions for each field

    /**
     * Validate client location input
     *
     * @param $value
     * @param ValidationResult $validationResult
     * @param bool $required
     * @return void
     */
    protected function validateUserId($value, ValidationResult $validationResult, bool $required = false): void
    {
        if (null !== $value && '' !== $value) {
            $this->validator->validateNumeric($value, 'user_id', $validationResult, $required);
            $this->validator->validateExistence($value, 'user', $validationResult, $required);
        } elseif (true === $required) {
            // If it is null or empty string and required
            $validationResult->setError('user_id', 'user_id is required but not given');
        }
    }

    /**
     * Validate client location input
     *
     * @param mixed $value
     * @param ValidationResult $validationResult
     * @param bool $required
     * @return void
     */
    protected function validateClientStatusId(
        mixed $value,
        ValidationResult $validationResult,
        bool $required = false
    ): void {
        if (null !== $value && '' !== $value) {
            $this->validator->validateNumeric($value, 'client_status_id', $validationResult, $required);
            $this->validator->validateExistence($value, 'client_status', $validationResult, $required);
        } elseif (true === $required) {
            // If it is null or empty string and required
            $validationResult->setError('client_status_id', 'client_status_id is required but not given');
        }
    }

    /**
     * Validate client location input
     *
     * @param $location
     * @param ValidationResult $validationResult
     * @param bool $required
     * @return void
     */
    protected function validateLocation($location, ValidationResult $validationResult, bool $required = false): void
    {
        if (null !== $location && '' !== $location) {
            $this->validator->validateLengthMax($location, 'location', $validationResult, 100);
            $this->validator->validateLengthMin($location, 'location', $validationResult, 3);
        } elseif (true === $required) {
            // If it is null or empty string and required
            $validationResult->setError('location', 'Location is required but not given');
        }
    }

    /**
     * Validate client phone input
     *
     * @param $value
     * @param ValidationResult $validationResult
     * @param bool $required
     * @return void
     */
    protected function validatePhone($value, ValidationResult $validationResult, bool $required = false): void
    {
        if (null !== $value && '' !== $value) {
            $this->validator->validateLengthMax($value, 'phone', $validationResult, 20);
            $this->validator->validateLengthMin($value, 'phone', $validationResult, 3);
        } elseif (true === $required) {
            // If it is null or empty string and required
            $validationResult->setError('phone', 'Phone is required but not given');
        }
    }

    /**
     * Validate client sex input
     *
     * @param $value
     * @param ValidationResult $validationResult
     * @param bool $required
     * @return void
     */
    protected function validateSex($value, ValidationResult $validationResult, bool $required = false): void
    {
        if (null !== $value && '' !== $value) {
            if (!in_array($value, ['M', 'F', 'O'])) {
                $validationResult->setError('sex', 'Invalid sex value given. Allowed are M, F and O');
            }
        } elseif (true === $required) {
            // If it is null or empty string and required
            $validationResult->setError('sex', 'Sex is required but not given');
        }
    }

    /**
     * Validate client phone input
     *
     * @param $value
     * @param ValidationResult $validationResult
     * @param bool $required
     * @return void
     */
    private function validateClientMessage($value, ValidationResult $validationResult, bool $required = false): void
    {
        if (null !== $value && '' !== $value) {
            $this->validator->validateLengthMax($value, 'client_message', $validationResult, 1000);
            $this->validator->validateLengthMin($value, 'client_message', $validationResult, 3);
        } elseif (true === $required) {
            // If it is null or empty string and required
            $validationResult->setError('phone', 'Phone is required but not given');
        }
    }



}
