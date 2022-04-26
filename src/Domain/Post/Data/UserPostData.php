<?php


namespace App\Domain\Post\Data;

use App\Common\ArrayReader;

/**
 * Post with user info
 */
class UserPostData
{
    public ?int $postId;
    public ?int $userId;
    public ?string $postMessage;
    public ?string $postCreatedAt;
    public ?string $postUpdatedAt;
    public ?string $userName;
    public ?string $userRole;

    // Not post value from db, populated in PostUserRightSetter
    public ?string $userMutationRight;

    public const MUTATION_PERMISSION_ALL = 'all';
    public const MUTATION_PERMISSION_NONE = 'none';

    /**
     * Post constructor.
     * @param array|null $postData
     */
    public function __construct(array $postData = null)
    {
        $arrayReader = new ArrayReader($postData);
        $this->postId = $arrayReader->findAsInt('post_id');
        $this->userId = $arrayReader->findAsInt('user_id');
        $this->postMessage = $arrayReader->findAsString('post_message');
        $this->postCreatedAt = $arrayReader->findAsString('post_created_at');
        $this->postUpdatedAt = $arrayReader->findAsString('post_updated_at');
        $this->userName = $arrayReader->findAsString('user_name');
        $this->userRole = $arrayReader->findAsString('user_role');
    }
}