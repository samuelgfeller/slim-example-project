<?php


namespace App\Domain\User\Service;


use App\Infrastructure\Post\PostRepository;
use App\Infrastructure\User\UserDeleterRepository;

class UserDeleter
{
    public function __construct(
        private PostRepository $postRepository,
        private UserDeleterRepository $userDeleterRepository
    )
    {
    }

    public function deleteUser($id): bool
    {
        $this->postRepository->deletePostsFromUser($id);
        return $this->userDeleterRepository->deleteUserById($id);
    }
}