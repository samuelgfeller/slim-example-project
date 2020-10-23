<?php


namespace App\Domain\Post;

use App\Domain\User\UserService;
use App\Infrastructure\Post\PostRepository;

/**
 * Business logic for post manipulation
 *
 * Class PostService
 * @package App\Domain\Post
 */
class PostService
{

    private PostRepository $postRepository;
    private UserService $userService;
    protected PostValidation $postValidation;

    public function __construct(
        PostRepository $postRepository,
        UserService $userService,
        PostValidation $postValidation
    ) {
        $this->postRepository = $postRepository;
        $this->userService = $userService;
        $this->postValidation = $postValidation;
    }

    /**
     * Gives all undeleted posts from db with name of user
     *
     * @return array
     */
    public function findAllPosts()
    {
        $allPosts = $this->postRepository->findAllPosts();
        return $this->populatePostsArrayWithUser($allPosts);
    }

    /**
     * Find one post in the database
     *
     * @param $id
     * @return array
     */
    public function findPost($id): array
    {
        return $this->postRepository->findPostById($id);
        // If in the future there are more than 1 call to this function and users are needed
        // Something similar to findAllPosts() could be done using populatePostsArrayWithUser().
        // For one usage (PostController:get()) I didn't think it's necessary to create an extra func
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
        return $this->populatePostsArrayWithUser($posts);
    }

    /**
     * Add user infos to post array
     *
     * @param $posts
     * @return array
     */
    private function populatePostsArrayWithUser($posts): array
    {
        // Add user name info to post
        $postsWithUser = [];
        foreach ($posts as $post) {
            // Get user information connected to post
            $user = $this->userService->findUser($post['user_id']);
            // If user was deleted but post not, post should not be shown since it is also technically deleted
            if (isset($user['name'])) {
                $post['user_name'] = $user['name'];
                $postsWithUser[] = $post;
            }
        }
        return $postsWithUser;
    }

    /**
     * Insert post in database
     *
     * @param Post $post
     * @return string
     */
    public function createPost(Post $post): string
    {
        $this->postValidation->validatePostCreationOrUpdate($post);
        return $this->postRepository->insertPost($post->toArray());
    }

    /**
     * Change something or multiple things on post
     *
     * @param Post $post
     * @return bool if update was successful
     */
    public function updatePost(Post $post): bool
    {
         $this->postValidation->validatePostCreationOrUpdate($post);
        return $this->postRepository->updatePost($post->toArray(), $post->getId());
    }

    /**
     * Mark one post as deleted
     *
     * @param $id
     * @return bool
     */
    public function deletePost($id): bool
    {
        return $this->postRepository->deletePost($id);
    }


}
