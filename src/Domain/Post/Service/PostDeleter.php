<?php


namespace App\Domain\Post\Service;


use App\Infrastructure\Post\PostRepository;

class PostDeleter
{
    public function __construct(
        private PostRepository $postRepository
    ) { }

    /**
     * Mark one post as deleted
     *
     * @param int $id
     * @return bool
     */
    public function deletePost(int $id): bool
    {
        return $this->postRepository->deletePost($id);
    }
}