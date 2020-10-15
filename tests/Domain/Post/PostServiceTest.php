<?php

namespace App\Test\Domain\Post;

use App\Domain\Post\PostService;
use App\Domain\User\UserService;
use App\Infrastructure\Post\PostRepository;
use App\Test\UnitTestUtil;
use PHPUnit\Framework\TestCase;

class PostServiceTest extends TestCase
{
    use UnitTestUtil;

    /**
     * Test function findAllPosts from PostService which returns
     * Post array including the name of users
     *
     * @dataProvider \App\Test\Domain\Post\PostProvider::oneSetOfMultiplePostsProvider()
     * @param array $posts
     */
    public function testFindAllPosts(array $posts)
    {
        // Add mock class PostRepository to container and define return value for method findAllPosts
        $this->mock(PostRepository::class)->method('findAllPosts')->willReturn($posts);

        // findAllPosts returns posts with the name of the according user
        // Only the name is relevant for the private function PostService:populatePostsArrayWithUser()
        $userName = 'John Example';
        $this->mock(UserService::class)->method('findUser')
            ->willReturn(['name' => $userName]);

        // Here we don't need to specify what the function will do / return since its exactly that
        // which is being tested. So we can take the autowired class instance from the container directly.
        $service = $this->container->get(PostService::class);

        // Add name of user to posts array
        $postsWithUsers = [];
        foreach ($posts as $post){
            $post['user_name'] = $userName;
            $postsWithUsers[] = $post;
        }
        // Not needed to test PostService:populatePostsArrayWithUser because if that function screws up
        // testFindAllPosts and other tests which use that function indirectly will fail since the user
        // is in the expected result of the assert

        self::assertEquals($postsWithUsers, $service->findAllPosts());

    }

//    public function testFindPost()
//    {
//    }
//
//    public function testFindAllPostsFromUser()
//    {
//    }
//
//    public function testCreatePost()
//    {
//    }
//
//    public function testUpdatePost()
//    {
//    }
//
//    public function testDeletePost()
//    {
//    }
}
