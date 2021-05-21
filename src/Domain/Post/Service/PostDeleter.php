<?php


namespace App\Domain\Post\Service;


use App\Infrastructure\Post\PostDeleterRepository;

class PostDeleter
{
    public function __construct(
        private PostDeleterRepository $postDeleterRepository
    ) { }

    /**
     * Mark one post as deleted
     *
     * @param int $id
     * @return bool
     */
    public function deletePost(int $id): bool
    {
        return $this->postDeleterRepository->deletePost($id);
    }
}