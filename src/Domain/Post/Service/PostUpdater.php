<?php


namespace App\Domain\Post\Service;


use App\Domain\Post\Data\PostData;
use App\Infrastructure\Post\PostUpdaterRepository;

class PostUpdater
{

    public function __construct(
        private PostValidator $postValidator,
        private PostUpdaterRepository $postUpdaterRepository
    ) { }

    /**
     * Change something or multiple things on post
     *
     * @param PostData $post
     * @return bool if update was successful
     */
    public function updatePost(PostData $post): bool
    {
        $this->postValidator->validatePostCreationOrUpdate($post);
        return $this->postUpdaterRepository->updatePost($post->toArray(), $post->id);
    }
}