<?php

namespace App\Domain\Client\Service;

use App\Domain\Client\Data\ClientData;
use App\Domain\Exceptions\ValidationException;
use App\Domain\Factory\LoggerFactory;
use App\Domain\Validation\AppValidation;
use App\Domain\Validation\ValidationResult;
use App\Infrastructure\Validation\ResourceExistenceCheckerRepository;

/**
 * Class PostValidator
 */
class ClientValidator extends AppValidation
{

    /**
     * PostValidator constructor.
     *
     * @param LoggerFactory $logger
     * @param ResourceExistenceCheckerRepository $userExistenceCheckerRepository
     */
    public function __construct(
        LoggerFactory $logger,
        private ResourceExistenceCheckerRepository $userExistenceCheckerRepository
    ) {
        parent::__construct(
            $logger->addFileHandler('error.log')->createInstance('post-validation')
        );
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

        $this->validateMessage($post->message, $validationResult, true);
        // It's a bit pointless to check user existence as user should always exist if he's logged in
        $this->validateUser($post->userId, $validationResult, true);

        $this->throwOnError($validationResult);
    }

    /**
     * Validate post update
     *
     * @param ClientData $client
     * @throws ValidationException
     */
    public function validateClientUpdate(ClientData $client): void
    {
        // Exact validation error tested in PostCaseProvider.php::providePostCreateInvalidData()
        $validationResult = new ValidationResult('There is something in the post data that couldn\'t be validated');

        if ($client->clientStatusId !== null) {
            $this->validateNumeric($client->clientStatusId, 'client_status_id', false, $validationResult);
            $this->validateExistence($client->clientStatusId, 'client_status', $validationResult);
        }
        if ($client->userId !== null) {
            $this->validateNumeric($client->userId, 'user_id', false, $validationResult);
            $this->validateExistence($client->userId, 'user', $validationResult);
        }

        if ($client->firstName !== null) {
            $this->validateName($client->firstName, 'first_name', false, $validationResult);
        }
        if ($client->lastName !== null) {
            $this->validateName($client->lastName, 'surname', false, $validationResult);
        }
        if ($client->email !== null) {
            $this->validateEmail($client->email, false, $validationResult);
        }

        // Todo birthdate, location, phone etc

        $this->throwOnError($validationResult);
    }

    protected function validateNumeric(
        string|null|int $numericValue,
        string $fieldName,
        bool $required,
        ValidationResult $validationResult,
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


    protected function validateMessage($postMsg, ValidationResult $validationResult, bool $required): void
    {
        if (null !== $postMsg && '' !== $postMsg) {
            $this->validateLengthMax($postMsg, 'message', $validationResult, 500);
            $this->validateLengthMin($postMsg, 'message', $validationResult, 4);
        } elseif (true === $required) {
            // If it is null or empty string and required
            $validationResult->setError('message', 'Message is required but not given');
        }
    }

    protected function validateUser($userId, ValidationResult $validationResult, bool $required): void
    {
        if (null !== $userId && '' !== $userId && $userId !== 0) {
            $this->validateExistence($userId, 'user', $validationResult);
        } elseif (true === $required) {
            // If it is null or empty string and required
            $validationResult->setError('user_id', 'user_id required but not given');
        }
    }

    /**
     * Check if user exists
     * Same function than in UserValidator.
     *
     * @param int $userId
     * @param ValidationResult $validationResult
     */
    protected function validateExistence(int $userId, string $table, ValidationResult $validationResult): void
    {
        $exists = $this->userExistenceCheckerRepository->rowExists($userId, $table);
        if (!$exists) {
            $validationResult->setMessage(mb_strtoupper($table) . ' not found');
            $validationResult->setError($table, mb_strtoupper($table) . ' not existing');

            $this->logger->debug("Checked for $table id $userId but it didn\'t exist in validation");
        }
    }

}
