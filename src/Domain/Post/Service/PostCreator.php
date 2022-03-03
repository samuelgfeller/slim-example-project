<?php


namespace App\Domain\Post\Service;


use App\Domain\Post\Data\PostData;
use App\Infrastructure\Post\PostCreatorRepository;

class PostCreator
{

    public function __construct(
        private PostValidator $postValidator,
        private PostCreatorRepository $postCreatorRepository
    ) { }

    /**
     * Post creation logic
     * Called by Action
     *
     * @param array $postData
     * @param int $loggedInUserId
     *
     * @return int insert id
     */
    public function createPost(array $postData, int $loggedInUserId): int
    {
        $post = new PostData($postData);
        $post->userId = $loggedInUserId;
        $this->postValidator->validatePostCreation($post);

        return $this->postCreatorRepository->insertPost($post->toArray());
    }
}