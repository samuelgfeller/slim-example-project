<?php


namespace App\Domain\Post;

use App\Domain\Post\PostRepositoryInterface;
use Firebase\JWT\JWT;


class PostService {
    
    private $postRepositoryInterface;
    
    public function __construct(PostRepositoryInterface $postRepositoryInterface) {
        $this->postRepositoryInterface = $postRepositoryInterface;
    }

    public function findAllPosts() {
        $allPosts= $this->postRepositoryInterface->findAllPosts();
        return $allPosts;
    }

    public function findPost($id): array
    {
        return $this->postRepositoryInterface->findPostById($id);
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
