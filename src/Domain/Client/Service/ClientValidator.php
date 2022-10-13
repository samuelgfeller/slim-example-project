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
     * @param ClientData $post
     * @throws ValidationException
     */
    public function validatePostCreation(ClientData $post): void
    {
        // Exact validation error tested in PostCaseProvider.php::providePostCreateInvalidData()
        $validationResult = new ValidationResult('There is something in the post data that couldn\'t be validated');

        $this->validateMessage($post->message, true, $validationResult);
        // It's a bit pointless to check user existence as user should always exist if he's logged in
        $this->validator->validateExistence($post->userId, 'user', $validationResult, true);

        $this->validator->throwOnError($validationResult);
    }

    /**
     * Validate post update
     *
     * @param ClientData $client
     * @param string|null $birthDateValue original value from request to validate the date
     */
    public function validateClientUpdate(ClientData $client, null|string $birthDateValue = null): void
    {
        // Exact validation error tested in PostCaseProvider.php::providePostCreateInvalidData()
        $validationResult = new ValidationResult('There is something in the client data that couldn\'t be validated');

        if ($client->clientStatusId !== null) {
            $this->validator->validateNumeric($client->clientStatusId, 'client_status_id', false, $validationResult);
            $this->validator->validateExistence($client->clientStatusId, 'client_status', $validationResult);
        }
        if ($client->userId !== null) {
            $this->validator->validateNumeric($client->userId, 'user_id', false, $validationResult);
            $this->validator->validateExistence($client->userId, 'user', $validationResult);
        }
        if ($client->firstName !== null) {
            $this->validator->validateName($client->firstName, 'first_name', false, $validationResult);
        }
        if ($client->lastName !== null) {
            $this->validator->validateName($client->lastName, 'surname', false, $validationResult);
        }
        if ($client->email !== null) {
            $this->validator->validateEmail($client->email, false, $validationResult);
        }
        if ($client->birthdate !== null) {
            $this->validator->validateBirthdate($client->birthdate, false, $validationResult);
            // Validate that date in object is the same as what the user submitted https://stackoverflow.com/a/19271434/9013718
            if ($birthDateValue !== null && $client->birthdate->format('Y-m-d') !== $birthDateValue) {
                $validationResult->setError('birthdate', 'Invalid birthdate. Instance not same as input.');
            }
        }
        if ($client->location !== null) {
            $this->validateLocation($client->location, false, $validationResult);
        }
        if ($client->phone !== null) {
            $this->validatePhone($client->phone, false, $validationResult);
        }
        if ($client->sex !== null) {
            $this->validateSex($client->sex, false, $validationResult);
        }

        $this->validator->throwOnError($validationResult);
    }


    /**
     * Validate client message input
     *
     * @param $postMsg
     * @param ValidationResult $validationResult
     * @param bool $required
     * @return void
     */
    protected function validateMessage($postMsg, bool $required, ValidationResult $validationResult): void
    {
        if (null !== $postMsg && '' !== $postMsg) {
            $this->validateLengthMax($postMsg, 'message', $validationResult, 500);
            $this->validateLengthMin($postMsg, 'message', $validationResult, 4);
        } elseif (true === $required) {
            // If it is null or empty string and required
            $validationResult->setError('message', 'Message is required but not given');
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
    protected function validateLocation($location, bool $required, ValidationResult $validationResult): void
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
    protected function validatePhone($value, bool $required, ValidationResult $validationResult): void
    {
        if (null !== $value && '' !== $value) {
            $this->validator->validateLengthMax($value, 'phone', $validationResult, 15);
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
    protected function validateSex($value, bool $required, ValidationResult $validationResult): void
    {
        if (null !== $value && '' !== $value) {
            if (!in_array($value, ['M', 'F', 'O'])){
                $validationResult->setError('sex', 'Invalid sex value given. Allowed are M, F and O.');
            }
        } elseif (true === $required) {
            // If it is null or empty string and required
            $validationResult->setError('sex', 'Sex is required but not given');
        }
    }


}
