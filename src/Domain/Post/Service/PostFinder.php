<?php


namespace App\Domain\Post\Service;


use App\Domain\Post\DTO\Post;

class PostFinder
{
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
        $post->user = $this->userService->findUserById($post->userId);
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
            $user = $this->userService->findUserById($post['user_id']);
            // If user was deleted but post not, post should not be shown since it is also technically deleted
            if ($user->name !== null) {
                $post->user = $user;
                $postsWithUser[] = $post;
            }
        }
        return $postsWithUser;
    }
}