<?php

namespace App\Domain\Post;

use App\Domain\Validation\AppValidation;
use App\Domain\Validation\ValidationResult;

/**
 * Class UserValidation
 */
class PostValidation extends AppValidation
{

    /**
     * Validate post creation or update since they are the same
     *
     * @param Post $post
     */
    public function validatePostCreationOrUpdate(Post $post): void
    {
        $validationResult = new ValidationResult('There is something in the post data which couldn\'t be validated');
        // In case message gets validated in other function
        $required = true;

        // Validate message
        if (null !== $post->getMessage()) {
            $this->validateLengthMax($post->getMessage(), 'message', $validationResult, 500);
            $this->validateLengthMin($post->getMessage(), 'message', $validationResult, 4);
        } elseif (true === $required) {
            // If it is null but required, the user input is faulty so bad request 400 return status is sent
            $validationResult->setIsBadRequest(true, 'message', 'Message is required but not given');
        }

        // todo does it make sense to check if user exists?

        $this->throwOnError($validationResult);
    }

}
