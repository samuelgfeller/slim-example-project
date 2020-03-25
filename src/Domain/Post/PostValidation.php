<?php

namespace App\Domain\Post;

use App\Domain\Post\PostRepositoryInterface;
use App\Domain\Validation\AppValidation;
use App\Domain\Validation\ValidationResult;
use Psr\Log\LoggerInterface;

/**
 * Class UserValidation
 *
 * @package App\Service\Validation
 */
class PostValidation extends AppValidation
{
    /**
     * @var PostRepositoryInterface
     */
    private $userRepositoryInterface;

    /**
     * UserValidation constructor.
     *
     * @param LoggerInterface $logger
     * @param PostRepositoryInterface $userRepositoryInterface
     */
    public function __construct(LoggerInterface $logger, PostRepositoryInterface $userRepositoryInterface)
    {
        parent::__construct($logger);
        $this->userRepositoryInterface = $userRepositoryInterface;
    }

    /**
     * Validate post creation.
     *
     * @param Post $post
     */
    public function validatePostCreationOrUpdate(Post $post): void
    {
        $validationResult = new ValidationResult('There is something in the post data which couldn\'t be validated');

        $this->validateLengthMax($post->getMessage(), 'message', $validationResult, 500);
        $this->validateLengthMin($post->getMessage(), 'message', $validationResult, 4);

        // todo does it make sense to check if user exists?

        $this->throwOnError($validationResult);
    }

}
