<?php

namespace App\Test\Unit\Domain\Post;

use App\Domain\Post\DTO\Post;
use App\Domain\Post\Service\PostFinder;
use App\Domain\User\DTO\User;
use App\Domain\User\Service\UserFinder;
use App\Infrastructure\Post\PostRepository;
use App\Test\AppTestTrait;
use PHPUnit\Framework\TestCase;

class PostFinderTest extends TestCase
{
    use AppTestTrait;

    /**
     * Test function findAllPostsWithUsers from PostService which returns
     * Post array including the the associated user
     *
     * @dataProvider \App\Test\Provider\PostProvider::oneSetOfMultiplePostsProvider()
     * @param Post[] $posts
     * @param User $user
     */
    public function testFindAllPosts(array $posts, User $user): void
    {
        // Add mock class PostRepository to container and define return value for method findAllPostsWithUsers
        $this->mock(PostRepository::class)->method('findAllPostsWithUsers')->willReturn($posts);
        // Service class findAllPostsWithUsers calls addUserToPosts
        $this->mock(UserFinder::class)->method('findUserById')->willReturn($user);


        // Here we don't need to specify what the service function will do / return since its exactly that
        // which is being tested. So we can take the autowired class instance from the container directly.
        /** @var PostFinder $service */
        $service = $this->container->get(PostFinder::class);


        self::assertEquals($posts, $service->findAllPostsWithUsers());

    }

    /**
     * Check if findPost() from PostService returns
     * the post coming from the repository
     *
     * @dataProvider \App\Test\Provider\PostProvider::onePostProvider()
     * @param array $post
     */
    public function testFindPost(array $post): void
    {
        // Add mock class PostRepository to container and define return value for method findPostById
        // I dont see the necessity of expecting method to be called. If we get the result we want
        // we can let the code free how it returns it (don't want annoying test that fails after slight code change)
        $this->mock(PostRepository::class)->method('findPostById')->willReturn(new Post($post));

        // Get an empty class instance from container
        /** @var PostFinder $service */
        $service = $this->container->get(PostFinder::class);

        self::assertEquals($post, $service->findPost($post['id']));
    }

    /**
     * Check if findAllPostsFromUser() from PostService returns
     * the posts coming from the repository AND
     * if the user names are contained in the returned array
     *
     * @dataProvider \App\Test\Provider\PostProvider::oneSetOfMultiplePostsProvider()
     * @param Post[] $posts
     */
    public function testFindAllPostsFromUser(array $posts): void
    {
        // Add mock class PostRepository to container and define return value for method findPostById
        // Posts are with different user_ids from provider and logically findAllPostsFromUser has to return
        // posts with the same user_id since they belong to the same user. But this is not the point of the test.
        // The same posts array will be used in the assertions
        $this->mock(PostRepository::class)->method('findAllPostsByUserId')->willReturn($posts);

        // findAllPostsWithUsers returns posts with the name of the according user so they have to be added here as well
        $postsWithUsersToCompare= $this->addUserToPostsForTesting($posts);

        /** @var PostFinder $service */
        $service = $this->container->get(PostFinder::class);

        // User id not relevant because return values from repo is defined above
        self::assertEquals($postsWithUsersToCompare, $service->findAllPostsFromUser(1));
    }

    /**
     * Replica of PostService:addUserToPosts
     *
     * User not added in PostProvider because only one provider is possible
     * and we need both one array of posts without users (to simulate
     * what comes from the repo) and one with users to assert
     *
     * Not needed to test PostService:addUserToPosts because if that function
     * screws up testfindAllPostsWithUsers and other tests which use that function indirectly will
     * fail since the user is in the expected result of the assert
     *
     * @param array $posts
     * @param User $user to be added to post
     * @return array
     */
    private function addUserToPostsForTesting(array $posts, User $user): array
    {

        // Add name of user to posts array
        $postsWithUsersToCompare = [];
        foreach ($posts as $post){
            $post['user_name'] = $userName;
            $postsWithUsersToCompare[] = $post;
        }
        return $postsWithUsersToCompare;
    }
}
