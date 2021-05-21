<?php


namespace App\Domain\User\Service;


use App\Infrastructure\Post\PostDeleterRepository;
use App\Infrastructure\User\UserDeleterRepository;

class UserDeleter
{
    public function __construct(
        private PostDeleterRepository $postDeleterRepository,
        private UserDeleterRepository $userDeleterRepository
    )
    {
    }

    public function deleteUser($id): bool
    {
        $this->postDeleterRepository->deletePostsFromUser($id);
        return $this->userDeleterRepository->deleteUserById($id);
    }
}