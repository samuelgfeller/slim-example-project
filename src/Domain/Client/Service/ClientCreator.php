<?php


namespace App\Domain\Client\Service;


use App\Domain\Client\Data\ClientData;
use App\Infrastructure\Post\PostCreatorRepository;

class ClientCreator
{

    public function __construct(
        private ClientValidator       $postValidator,
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
        $post = new ClientData($postData);
        $post->userId = $loggedInUserId;
        $this->postValidator->validatePostCreation($post);

        return $this->postCreatorRepository->insertPost($post->toArrayForDatabase());
    }
}