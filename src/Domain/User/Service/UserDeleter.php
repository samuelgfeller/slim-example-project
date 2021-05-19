<?php


namespace App\Domain\User\Service;


use App\Infrastructure\Post\PostRepository;
use App\Infrastructure\User\UserRepository;

class UserDeleter
{
    public function __construct(
        private PostRepository $postRepository,
        private UserRepository $userRepository
    ) { }

    public function deleteUser($id): bool
    {
        $this->postRepository->deletePostsFromUser($id);
        return $this->userRepository->deleteUserById($id);
    }
}