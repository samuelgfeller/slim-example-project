<?php


namespace App\Domain\Post\Service;


use App\Domain\Post\DTO\Post;
use App\Domain\Post\DTO\UserPost;
use App\Domain\User\Service\UserFinder;
use App\Infrastructure\Post\PostFinderRepository;

class PostFinder
{
    public function __construct(
        private PostFinderRepository $postFinderRepository,
        private UserFinder $userFinder
    ) { }

    /**
     * Gives all undeleted posts from db with name of user
     *
     * @return Post[]
     */
    public function findAllPostsWithUsers(): array
    {
        return $this->postFinderRepository->findAllPostsWithUsers();
    }

    /**
     * Find one post in the database
     *
     * @param $id
     * @return Post
     */
    public function findPost($id): Post
    {
        $post = $this->postFinderRepository->findPostById($id);
        $post->user = $this->userFinder->findUserById($post->userId);
        return $post;
    }

    /**
     * Find specific post with user info
     *
     * @param int $id
     * @return UserPost
     */
    public function findPostWithUserById(int $id): UserPost
    {
        return $this->postFinderRepository->findUserPostById($id);
    }

    /**
     * Return all posts which are linked to the given user
     *
     * @param int $userId
     * @return UserPost[]
     */
    public function findAllPostsFromUser(int $userId): array
    {
        return $this->postFinderRepository->findAllPostsByUserId($userId);
    }
}