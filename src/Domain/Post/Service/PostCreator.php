<?php


namespace App\Domain\Post\Service;


use App\Domain\Post\DTO\Post;
use App\Infrastructure\Post\PostRepository;

class PostCreator
{

    public function __construct(
        private PostValidator $postValidator,
        private PostRepository $postRepository
    ) { }

    /**
     * Insert post in database
     *
     * @param Post $post
     * @return int insert id
     */
    public function createPost(Post $post): int
    {
        $this->postValidator->validatePostCreationOrUpdate($post);
        return $this->postRepository->insertPost($post->toArray());
    }
}