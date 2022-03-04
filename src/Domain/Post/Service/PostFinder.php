<?php


namespace App\Domain\Post\Service;


use App\Domain\Post\Data\PostData;
use App\Domain\Post\Data\UserPostData;
use App\Domain\User\Service\UserFinder;
use App\Infrastructure\Post\PostFinderRepository;

class PostFinder
{
    public function __construct(
        private PostFinderRepository $postFinderRepository,
    ) { }

    /**
     * Gives all undeleted posts from db with name of user
     *
     * @return PostData[]
     */
    public function findAllPostsWithUsers(): array
    {
        return $this->postFinderRepository->findAllPostsWithUsers();
    }

    /**
     * Find one post in the database
     *
     * @param $id
     * @return PostData
     */
    public function findPost($id): PostData
    {
        return $this->postFinderRepository->findPostById($id);
    }

    /**
     * Return all posts which are linked to the given user
     *
     * @param int $userId
     * @return UserPostData[]
     */
    public function findAllPostsFromUser(int $userId): array
    {
        return $this->postFinderRepository->findAllPostsByUserId($userId);
    }
}