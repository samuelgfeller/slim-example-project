<?php

namespace App\Domain\Client\Service;

use App\Domain\Client\Data\ClientData;
use App\Domain\Client\Enum\ClientVigilanceLevel;
use App\Domain\Factory\LoggerFactory;
use App\Domain\Validation\ValidationResult;
use App\Domain\Validation\Validator;

/**
 * Client user input validator.
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
     * Validate client creation.
     *
     * @param ClientData $client
     * @param string|null $birthdateValue
     */
    public function validateClientCreation(ClientData $client, null|string $birthdateValue = null): void
    {
        // Exact validation error tested in ClientCreateActionTest
        $validationResult = new ValidationResult('There is something in the client data that couldn\'t be validated');

        $this->validateClientStatusId($client->clientStatusId, $validationResult, true);

        // User id should not be required when client is created from authenticated area and public portal
        $this->validateUserId($client->userId, $validationResult, false);

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

        $this->validator->validateBackedEnum(
            $client->vigilanceLevel,
            ClientVigilanceLevel::class,
            'vigilance_level',
            $validationResult,
            false
        );

        $this->validator->throwOnError($validationResult);
    }

    /**
     * Validate post update.
     *
     * @param array $clientValues values that user wants to change
     */
    public function validateClientUpdate(array $clientValues): void
    {
        // Exact validation error tested in PostCaseProvider.php::providePostCreateInvalidData()
        $validationResult = new ValidationResult('There is something in the client data that couldn\'t be validated');

        // Using array_key_exists instead of isset as isset returns false if value is null and key exists
        if (array_key_exists('client_status_id', $clientValues)) {
            $this->validateClientStatusId($clientValues['client_status_id'], $validationResult, false);
        }
        if (array_key_exists('user_id', $clientValues)) {
            $this->validateUserId($clientValues['user_id'], $validationResult, false);
        }
        if (array_key_exists('first_name', $clientValues)) {
            $this->validator->validateName($clientValues['first_name'], 'first_name', $validationResult, false);
        }
        if (array_key_exists('last_name', $clientValues)) {
            $this->validator->validateName($clientValues['last_name'], 'last_name', $validationResult, false);
        }
        if (array_key_exists('email', $clientValues)) {
            $this->validator->validateEmail($clientValues['email'], $validationResult, false);
        }
        if (array_key_exists('birthdate', $clientValues)) {
            $this->validator->validateBirthdate($clientValues['birthdate'], $validationResult, false);
        }
        if (array_key_exists('location', $clientValues)) {
            $this->validateLocation($clientValues['location'], $validationResult, false);
        }
        if (array_key_exists('phone', $clientValues)) {
            $this->validatePhone($clientValues['phone'], $validationResult, false);
        }
        if (array_key_exists('sex', $clientValues)) {
            $this->validateSex($clientValues['sex'], $validationResult, false);
        }
        if (array_key_exists('vigilance_level', $clientValues)) {
            $this->validator->validateBackedEnum(
                $clientValues['vigilance_level'],
                ClientVigilanceLevel::class,
                'vigilance_level',
                $validationResult,
                false
            );
        }

        $this->validator->throwOnError($validationResult);
    }

    // Validate functions for each field

    /**
     * Validate client user id dropdown.
     *
     * @param $value
     * @param ValidationResult $validationResult
     * @param bool $required
     *
     * @return void
     */
    protected function validateUserId($value, ValidationResult $validationResult, bool $required = false): void
    {
        if (null !== $value && '' !== $value) {
            $this->validator->validateNumeric($value, 'user_id', $validationResult, $required);
            $this->validator->validateExistence((int)$value, 'user', $validationResult, $required);
        } elseif (true === $required) {
            // If it is null or empty string and required
            $validationResult->setError('user_id', 'user_id is required');
        }
    }

    /**
     * Validate client status dropdown.
     *
     * @param mixed $value
     * @param ValidationResult $validationResult
     * @param bool $required
     *
     * @return void
     */
    protected function validateClientStatusId(
        mixed $value,
        ValidationResult $validationResult,
        bool $required = false
    ): void {
        if (null !== $value && '' !== $value) {
            $this->validator->validateNumeric($value, 'client_status_id', $validationResult, $required);
            $this->validator->validateExistence((int)$value, 'client_status', $validationResult, $required);
        } elseif (true === $required) {
            // If it is null or empty string and required
            $validationResult->setError('client_status_id', 'client_status_id is required');
        }
    }

    /**
     * Validate client location input.
     *
     * @param $location
     * @param ValidationResult $validationResult
     * @param bool $required
     *
     * @return void
     */
    protected function validateLocation($location, ValidationResult $validationResult, bool $required = false): void
    {
        if (null !== $location && '' !== $location) {
            $this->validator->validateLengthMax($location, 'location', $validationResult, 100);
            $this->validator->validateLengthMin($location, 'location', $validationResult, 2);
        } elseif (true === $required) {
            // If it is null or empty string and required
            $validationResult->setError('location', 'Location is required');
        }
    }

    /**
     * Validate client phone input.
     *
     * @param $value
     * @param ValidationResult $validationResult
     * @param bool $required
     *
     * @return void
     */
    protected function validatePhone($value, ValidationResult $validationResult, bool $required = false): void
    {
        if (null !== $value && '' !== $value) {
            $this->validator->validateLengthMax($value, 'phone', $validationResult, 20);
            $this->validator->validateLengthMin($value, 'phone', $validationResult, 3);
        } elseif (true === $required) {
            // If it is null or empty string and required
            $validationResult->setError('phone', 'Phone is required');
        }
    }

    /**
     * Validate client sex options.
     *
     * @param $value
     * @param ValidationResult $validationResult
     * @param bool $required
     *
     * @return void
     */
    protected function validateSex($value, ValidationResult $validationResult, bool $required = false): void
    {
        if (null !== $value && '' !== $value) {
            if (!in_array($value, ['M', 'F', 'O', ''], true)) {
                $validationResult->setError('sex', 'Invalid sex value given.');
            }
        } elseif (true === $required) {
            // If it is null or empty string and required
            $validationResult->setError('sex', 'Sex is required');
        }
    }

    /**
     * Validate client message input.
     *
     * @param $value
     * @param ValidationResult $validationResult
     * @param bool $required
     *
     * @return void
     */
    private function validateClientMessage($value, ValidationResult $validationResult, bool $required = false): void
    {
        if (null !== $value && '' !== $value) {
            $this->validator->validateLengthMax($value, 'client_message', $validationResult, 1000);
            $this->validator->validateLengthMin($value, 'client_message', $validationResult, 3);
        } elseif (true === $required) {
            // If it is null or empty string and required
            $validationResult->setError('client_message', 'Message is required');
        }
    }
}
