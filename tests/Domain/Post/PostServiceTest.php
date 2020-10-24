<?php

namespace App\Test\Domain\Post;

use App\Domain\Post\Post;
use App\Domain\Post\PostService;
use App\Domain\User\UserService;
use App\Domain\Utility\ArrayReader;
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
        $postsWithUsersToCompare = $this->populatePostsArrayWithUserForTesting($posts);

        // Here we don't need to specify what the function will do / return since its exactly that
        // which is being tested. So we can take the autowired class instance from the container directly.
        /** @var PostService $postService */
        $service = $this->container->get(PostService::class);

        self::assertEquals($postsWithUsersToCompare, $service->findAllPosts());

    }

    /**
     * Check if findPost() from PostService returns
     * the post coming from the repository
     *
     * @dataProvider \App\Test\Domain\Post\PostProvider::onePostProvider()
     * @param array $post
     */
    public function testFindPost(array $post)
    {
        // Add mock class PostRepository to container and define return value for method findPostById
        $this->mock(PostRepository::class)->method('findPostById')->willReturn($post);

        // Get an empty class instance from container
        /** @var PostService $postService */
        $service = $this->container->get(PostService::class);

        self::assertEquals($post, $service->findPost($post['id']));
    }

    /**
     * Check if findAllPostsFromUser() from PostService returns
     * the posts coming from the repository AND
     * if the user names are contained in the returned array
     *
     * @dataProvider \App\Test\Domain\Post\PostProvider::oneSetOfMultiplePostsProvider()
     * @param array $posts
     */
    public function testFindAllPostsFromUser(array $posts)
    {
        // Add mock class PostRepository to container and define return value for method findPostById
        // Posts are with different user_ids from provider and logically findAllPostsFromUser has to return
        // posts with the same user_id since they belong to the same user. But this is not the point of the test.
        // The same posts array will be used in the assertions
        $this->mock(PostRepository::class)->method('findAllPostsByUserId')->willReturn($posts);

        // findAllPosts returns posts with the name of the according user so they have to be added here as well
        $postsWithUsersToCompare= $this->populatePostsArrayWithUserForTesting($posts);

        /** @var PostService $postService */
        $service = $this->container->get(PostService::class);

        // User id not relevant because return values from repo is defined above
        self::assertEquals($postsWithUsersToCompare, $service->findAllPostsFromUser(1));
    }

    /**
     * Test that service method createPost() calls PostRepository:insertPost()
     * and that (service) createPost() returns the id returned from (repo) insertPost()
     *
     * @dataProvider \App\Test\Domain\Post\PostProvider::onePostProvider()
     * @param array $validPost
     */
    public function testCreatePost(array $validPost)
    {
        $postId = (string)$validPost['id'];

        // Removing id from post array because before post is created id is not known
        unset($validPost['id']);

        // Mock the required repository and configure relevant method return value
        $this->mock(PostRepository::class)->method('insertPost')->willReturn($postId);

        /** @var PostService $postService */
        $postService = $this->container->get(PostService::class);

        $postObj = new Post(new ArrayReader($validPost));

        $postService->createPost($postObj);

        self::assertEquals($postId, $postService->createPost($postObj));
    }
//
//    public function testUpdatePost()
//    {
//    }
//
//    public function testDeletePost()
//    {
//    }

    /**
     * Replica of PostService:populatePostsArrayWithUser
     *
     * User not added in PostProvider because only one provider is possible
     * and we need both one array of posts without users (to simulate
     * what comes from the repo) and one with users to assert
     *
     * Not needed to test PostService:populatePostsArrayWithUser because if that function
     * screws up testFindAllPosts and other tests which use that function indirectly will
     * fail since the user is in the expected result of the assert
     *
     * @param array $posts
     * @return array
     */
    private function populatePostsArrayWithUserForTesting(array $posts): array
    {
        // Only the name is relevant for the private function PostService:populatePostsArrayWithUser()
        $userName = 'John Example';
        $this->mock(UserService::class)->method('findUser')
            ->willReturn(['name' => $userName]);

        // Add name of user to posts array
        $postsWithUsersToCompare = [];
        foreach ($posts as $post){
            $post['user_name'] = $userName;
            $postsWithUsersToCompare[] = $post;
        }
        return $postsWithUsersToCompare;
    }
}
