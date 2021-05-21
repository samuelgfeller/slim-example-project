<?php


namespace App\Domain\Post\DTO;

use App\Common\ArrayReader;

/**
 * Post with user info
 */
class UserPost
{
    public ?int $postId;
    public ?int $userId;
    public ?string $postMessage;
    public ?string $postCreatedAt;
    public ?string $postUpdatedAt;
    public ?string $userName;
    public ?string $userRole;

    /**
     * Post constructor.
     * @param array|null $postData
     */
    public function __construct(array $postData = null)
    {
        $arrayReader = new ArrayReader($postData);
        $this->postId = $arrayReader->findInt('post_id');
        $this->userId = $arrayReader->findInt('user_id');
        $this->postMessage = $arrayReader->findString('post_message');
        $this->postCreatedAt = $arrayReader->findString('post_created_at');
        $this->postUpdatedAt = $arrayReader->findString('post_updated_at');
        $this->userName = $arrayReader->findString('user_name');
        $this->userRole = $arrayReader->findString('user_role');
    }
}