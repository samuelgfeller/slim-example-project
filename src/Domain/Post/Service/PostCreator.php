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
     * Insert post in database
     *
     * @param PostData $post
     * @return int insert id
     */
    public function createPost(PostData $post): int
    {
        $this->postValidator->validatePostCreation($post);
        return $this->postCreatorRepository->insertPost($post->toArray());
    }
}