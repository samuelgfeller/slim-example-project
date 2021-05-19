<?php


namespace App\Domain\Post\Service;


use App\Domain\Post\DTO\Post;
use App\Infrastructure\Post\PostRepository;

class PostUpdater
{

    public function __construct(
        private PostValidator $postValidator,
        private PostRepository $postRepository
    ) { }

    /**
     * Change something or multiple things on post
     *
     * @param Post $post
     * @return bool if update was successful
     */
    public function updatePost(Post $post): bool
    {
        $this->postValidator->validatePostCreationOrUpdate($post);
        return $this->postRepository->updatePost($post->toArray(), $post->id);
    }
}