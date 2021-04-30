<?php

namespace App\Domain\Post;

use App\Domain\Exceptions\ValidationException;
use App\Domain\Factory\LoggerFactory;
use App\Domain\Validation\AppValidation;
use App\Domain\Validation\ValidationResult;
use App\Infrastructure\User\UserRepository;

/**
 * Class UserValidation
 */
class PostValidation extends AppValidation
{
    /** @var UserRepository */
    private UserRepository $userRepository;

    /**
     * UserValidation constructor.
     *
     * @param LoggerFactory $logger
     * @param UserRepository $userRepository
     */
    public function __construct(LoggerFactory $logger, UserRepository $userRepository)
    {
        parent::__construct($logger->addFileHandler('error.log')
            ->createInstance('post-validation'));
        $this->userRepository = $userRepository;
    }

    /**
     * Validate post creation or update since they are the same
     *
     * @param Post $post
     * @throws ValidationException
     */
    public function validatePostCreationOrUpdate(Post $post): void
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
     * Same function than in UserValidation. Here again because as the functionalities
     * grow, there could be other uses for the UserRepository. Maybe not though.
     *
     * @param $userId
     * @param ValidationResult $validationResult
     */
    protected function validateUserExistence($userId, ValidationResult $validationResult): void
    {
        $exists = $this->userRepository->userExists($userId);
        if (!$exists) {
            $validationResult->setMessage('User not found');
            $validationResult->setError('user', 'User not existing');

            $this->logger->debug('Check for user (id: ' . $userId . ') that didn\'t exist in validation');
        }
    }

}
