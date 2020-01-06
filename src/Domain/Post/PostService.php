<?php


namespace App\Domain\Post;

use App\Domain\Post\PostRepositoryInterface;
use App\Domain\User\UserService;
use Firebase\JWT\JWT;


class PostService
{

    private $postRepositoryInterface;
    private $userService;

    public function __construct(PostRepositoryInterface $postRepositoryInterface, UserService $userService)
    {
        $this->postRepositoryInterface = $postRepositoryInterface;
        $this->userService = $userService;
    }

    public function findAllPosts()
    {
        $allPosts = $this->postRepositoryInterface->findAllPosts();
        return $this->populatePostsArrayWithUser($allPosts);
    }

    public function findPost($id): array
    {
        return $this->postRepositoryInterface->findPostById($id);
    }

    /**
     * Return all posts which are linked to the given user
     *
     * @param $userId
     * @return array
     */
    public function findAllPostsFromUser($userId): array
    {
        $posts = $this->postRepositoryInterface->findAllPostsByUserId($userId);
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
            $post['user_name'] = $user['name'];
            $postsWithUser[] = $post;
        }
        return $postsWithUser;
    }

    /**
     * Insert post in database
     *
     * @param $data
     * @return string
     */
    public function createPost($data): string
    {
        return $this->postRepositoryInterface->insertPost($data);
    }


    public function updatePost($id, $data): bool
    {
        $userData = [
            'message' => $data['message'],
        ];

        return $this->postRepositoryInterface->updatePost($userData, $id);
    }

    public function deletePost($id): bool
    {
        return $this->postRepositoryInterface->deletePost($id);
    }


}
