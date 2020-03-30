<?php


namespace App\Domain\Post;

use App\Domain\User\UserService;
use App\Infrastructure\Persistence\Post\PostRepository;


class PostService
{

    private PostRepository $postRepository;
    private $userService;
    protected $postValidation;

    public function __construct(
        PostRepository $postRepository,
        UserService $userService,
        PostValidation $postValidation
    ) {
        $this->postRepository = $postRepository;
        $this->userService = $userService;
        $this->postValidation = $postValidation;
    }

    public function findAllPosts()
    {
        $allPosts = $this->postRepository->findAllPosts();
        return $this->populatePostsArrayWithUser($allPosts);
    }

    public function findPost($id): array
    {
        return $this->postRepository->findPostById($id);
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
            // If user was deleted but post not
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


    public function updatePost(Post $post): bool
    {
         $this->postValidation->validatePostCreationOrUpdate($post);
        return $this->postRepository->updatePost($post->toArray(), $post->getId());
    }

    public function deletePost($id): bool
    {
        return $this->postRepository->deletePost($id);
    }


}
