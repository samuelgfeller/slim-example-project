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
        // todo output escaping
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
        $token = $data['user'];
        $jwt = JWT::decode($token,'test',['HS256']);

        if(!empty($jwt)){
            return $this->postRepositoryInterface->insertPost([
                'message' => $data['message'],
                'user_id' => $jwt->data->userId
            ]);
        }
        return "Error could not create";
    }

    public function updatePost($id,$data): bool
    {
        $userData = [
            'message' => $data['message'],
            'user' => $data['user']
        ];

        $jwt = JWT::decode($userData['user'],'test',['HS256']);

        $post = $this->postRepositoryInterface->findPostById($id);
        if(!empty($jwt) && !empty($post)) {
            if ($jwt->data->userId == $post['user_id']) {
                return $this->postRepositoryInterface->updatePost([
                    "message" => $userData['message'],
                    "user_id" => $jwt->data->userId
                ], $id);
            }
        } return false;
    }

    public function deletePost($id): bool
    {
        return $this->postRepositoryInterface->deletePost($id);
    }


}
