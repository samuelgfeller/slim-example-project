<?php

namespace App\Test\Unit\Post;

use App\Domain\Post\Data\UserPostData;
use App\Domain\Post\Exception\InvalidPostFilterException;
use App\Domain\Post\Service\PostFilterFinder;
use App\Domain\Post\Service\PostFinder;
use App\Infrastructure\Post\PostFinderRepository;
use App\Test\Provider\Post\PostDataProvider;
use App\Test\Traits\AppTestTrait;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\TestCase;

/**
 * In this test class I intended to test the things below, but I intentionally won't do it.
 * Reason: 1. these cases are already integration tested, 2 it's a pain to write them beautifully
 * (because of the filter logic) and 3, it will be annoying to maintain those in addition to the integration tests.
 * - find all posts without filter
 * - find posts with valid filter
 * - find posts with invalid filter
 * - find posts with filter ['user' => 'session'] while being logged-in
 * - find posts with filter ['user' => 'session'] while not being logged-in
 */
class PostFilterFinderTest extends TestCase
{
    use AppTestTrait;

    /**
     * Test function findPostsWithFilter from PostFilterFinder
     *
     * @dataProvider \App\Test\Provider\Post\PostDataProvider::oneSetOfMultipleUserPostsProvider()
     * @param UserPostData[] $userPosts
     */
    public function testFindPostsWithFilter_noFilter(array $userPosts): void
    {
        // Add mock class PostFinderRepository to container and define return value for method findAllPostsWithUsers
        $this->mock(PostFinderRepository::class)->method('findAllPostsWithUsers')->willReturn($userPosts);

        // Get autowired class from container
        /** @var PostFinder $service */
        $service = $this->container->get(PostFinder::class);

        self::assertEquals($userPosts, $service->findAllPostsWithUsers());
    }


    /**
     * Test that function findPostsWithFilter from PostFilterFinder
     * returns the user posts linked to user from mocked repository.
     *
     * This function serves as an example of how I would begin to unit test but
     * as mentioned in the class php-doc I won't pursue unit testing on that
     * function.
     *
     * @dataProvider \App\Test\Provider\Post\PostFilterUnitCaseProvider::provideFilter_user()
     * @param array $filterParams
     */
    public function testFindPostsWithFilter_user(array $filterParams): void
    {
        // Get multiple posts with user details attached to the same user
        /** @var UserPostData[] $userPosts */
        $userPosts = (new PostDataProvider())->oneSetOfMultipleUserPostsProvider()[0]['posts'];

        // Simulate logged-in user with same id as $userPosts
        $this->container->get(SessionInterface::class)->set('user_id', $userPosts[0]->postId);

        // Add mock class PostFinderRepository to container and define return value for method findAllPostsWithUsers
        $this->mock(PostFinderRepository::class)->method('findAllPostsByUserId')->willReturn($userPosts);

        /** @var PostFilterFinder $service */
        $service = $this->container->get(PostFilterFinder::class);

        // If filterParams is invalid, throw exception
        if (!is_numeric($filterParams['user'])){
            $this->expectException(InvalidPostFilterException::class);
        }

        self::assertEquals($userPosts, $service->findPostsWithFilter($filterParams));
    }
}
