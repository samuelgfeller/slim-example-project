<?php


namespace App\Domain\Post;

use App\Domain\Post\PostRepositoryInterface;

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

    public function findPostByEmail($email): array
    {
        return $this->postRepositoryInterface->findPostByEmail($email);
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

    public function updatePost($id,$name,$email): bool
    {
        $data = [
            'name' => $name,
            'email' => $email
        ];
        return $this->postRepositoryInterface->updatePost($data,$id);
    }

    public function deletePost($id): bool
    {
        return $this->postRepositoryInterface->deletePost($id);
    }


}
