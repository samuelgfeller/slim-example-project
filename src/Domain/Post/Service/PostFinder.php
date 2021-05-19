<?php


namespace App\Domain\Post\Service;


use App\Domain\Post\DTO\Post;
use App\Domain\User\Service\UserFinder;
use App\Infrastructure\Post\PostRepository;

class PostFinder
{
    public function __construct(
        private PostRepository $postRepository,
        private UserFinder $userFinder
    ) { }

    /**
     * Gives all undeleted posts from db with name of user
     *
     * @return Post[]
     */
    public function findAllPostsWithUsers(): array
    {
        $allPosts = $this->postRepository->findAllPostsWithUsers();
        return $this->addUserToPosts($allPosts);
    }

    /**
     * Find one post in the database
     *
     * @param $id
     * @return Post
     */
    public function findPost($id): Post
    {
        $post = $this->postRepository->findPostById($id);
        $post->user = $this->userFinder->findUserById($post->userId);
        return $post;
    }

    /**
     * Return all posts which are linked to the given user
     *
     * @param $userId
     * @return array
     */
    public function findAllPostsFromUser($userId): array
    {
        $posts = $this->postRepository->findAllPostsByUserId($userId);
        return $this->addUserToPosts($posts);
    }

    /**
     * Add user infos to post array
     *
     * @param Post[] $posts
     * @return array
     */
    private function addUserToPosts(array $posts): array
    {
        // Add user name info to post
        $postsWithUser = [];
        foreach ($posts as $post) {
            // Get user information connected to post
            $user = $this->userFinder->findUserById($post['user_id']);
            // If user was deleted but post not, post should not be shown since it is also technically deleted
            if ($user->name !== null) {
                $post->user = $user;
                $postsWithUser[] = $post;
            }
        }
        return $postsWithUser;
    }
}