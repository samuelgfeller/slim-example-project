<?php

namespace App\Domain\Post;

use App\Domain\Validation\AppValidation;
use App\Domain\Validation\ValidationResult;
use App\Infrastructure\User\UserRepository;
use Psr\Log\LoggerInterface;

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
     * @param LoggerInterface $logger
     * @param UserRepository $userRepository
     */
    public function __construct(LoggerInterface $logger, UserRepository $userRepository)
    {
        parent::__construct($logger);
        $this->userRepository = $userRepository;
    }

    /**
     * Validate post creation or update since they are the same
     *
     * @param Post $post
     */
    public function validatePostCreationOrUpdate(Post $post): void
    {
        $validationResult = new ValidationResult('There is something in the post data that couldn\'t be validated');
        // In case message gets validated in other function
        $required = true;

        $postMsg = $post->getMessage();

        // Validate message
        if (null !== $postMsg && '' !== $postMsg){
            $this->validateLengthMax($postMsg, 'message', $validationResult, 500);
            $this->validateLengthMin($postMsg, 'message', $validationResult, 4);
        } elseif (true === $required) {
            // If it is null but required, the user input is faulty so bad request 400 return status is sent
            $validationResult->setIsBadRequest(true, 'message', 'Message is required but not given');
        }

        // Check if user exists
        $this->validateUserExistence($post->getUserId(), $validationResult);

        $this->throwOnError($validationResult);
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
