<?php

namespace App\Domain\Post\Service;

use App\Domain\Exceptions\ValidationException;
use App\Domain\Factory\LoggerFactory;
use App\Domain\Post\Data\PostData;
use App\Domain\Validation\AppValidation;
use App\Domain\Validation\ValidationResult;
use App\Infrastructure\User\UserExistenceCheckerRepository;

/**
 * Class PostValidator
 */
class PostValidator extends AppValidation
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
    )
    {
        parent::__construct($logger->addFileHandler('error.log')
            ->createInstance('post-validation'));

    }

    /**
     * Validate post creation or update since they are the same
     *
     * @param PostData $post
     * @throws ValidationException
     */
    public function validatePostCreationOrUpdate(PostData $post): void
    {
        $validationResult = new ValidationResult('There is something in the post data that couldn\'t be validated');

        $this->validateMessage($post->message, $validationResult, true);
        $this->validateUser($post->userId, $validationResult, true);

        $this->throwOnError($validationResult);
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
     * @param $userId
     * @param ValidationResult $validationResult
     */
    protected function validateUserExistence($userId, ValidationResult $validationResult): void
    {
        $exists = $this->userExistenceCheckerRepository->userExists($userId);
        if (!$exists) {
            $validationResult->setMessage('User not found');
            $validationResult->setError('user', 'User not existing');

            $this->logger->debug('Check for user (id: ' . $userId . ') that didn\'t exist in validation');
        }
    }

}
