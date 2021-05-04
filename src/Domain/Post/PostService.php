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

    // Service (and repo) should be split in more specific parts if it gets too big or has a lot of dependencies
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
     * @return Post[]
     */
    public function findAllPosts(): array
    {
        $allPosts = $this->postRepository->findAllPosts();
        return $this->populatePostsArrayWithUser($allPosts);
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
        return $this->populatePostsArrayWithUser($posts);
    }

    /**
     * Add user infos to post array
     *
     * @param Post[] $posts
     * @return array
     */
    private function populatePostsArrayWithUser(array $posts): array
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

    /**
     * Insert post in database
     *
     * @param Post $post
     * @return int insert id
     */
    public function createPost(Post $post): int
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
        return $this->postRepository->updatePost($post->toArray(), $post->id);
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
