<?php

namespace App\Domain\Client\Service;

use App\Domain\Exceptions\ValidationException;
use App\Domain\Factory\LoggerFactory;
use App\Domain\Client\Data\ClientData;
use App\Domain\Validation\AppValidation;
use App\Domain\Validation\ValidationResult;
use App\Infrastructure\User\UserExistenceCheckerRepository;

/**
 * Class PostValidator
 */
class ClientValidator extends AppValidation
{

    /**
     * PostValidator constructor.
     *
     * @param LoggerFactory $logger
     * @param UserExistenceCheckerRepository $userExistenceCheckerRepository
     */
    public function __construct(
        LoggerFactory $logger,
        private UserExistenceCheckerRepository $userExistenceCheckerRepository
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
        }
        if ($client->userId !== null) {
            $this->validateNumeric($client->userId, 'user_id', false, $validationResult);
            $this->validateUserExistence($client->userId, $validationResult);
        }

        if ($client->firstName !== null) {
            $this->validateName($client->firstName, 'first_name', false, $validationResult);
        }
        if ($client->last_name !== null) {
            $this->validateName($client->last_name, 'surname', false, $validationResult);
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
            $this->validateUserExistence($userId, $validationResult);
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
    protected function validateUserExistence(int $userId, ValidationResult $validationResult): void
    {
        $exists = $this->userExistenceCheckerRepository->userExists($userId);
        if (!$exists) {
            $validationResult->setMessage('User not found');
            $validationResult->setError('user', 'User not existing');

            $this->logger->debug('Check for user (id: ' . $userId . ') that didn\'t exist in validation');
        }
    }

}
