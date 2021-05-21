<?php

namespace App\Test\Unit\Domain\Post;

use App\Domain\Exceptions\ValidationException;
use App\Domain\Post\DTO\Post;
use App\Domain\Post\Service\PostCreator;
use App\Infrastructure\Post\PostRepository;
use App\Infrastructure\User\UserExistenceCheckerRepository;
use App\Test\AppTestTrait;
use PHPUnit\Framework\TestCase;

class PostCreatorTest extends TestCase
{
    use AppTestTrait;

    /**
     * Test that service method createPost() calls PostRepository:insertPost()
     * and that (service) createPost() returns the id returned from (repo) insertPost()
     *
     * @dataProvider \App\Test\Provider\PostProvider::onePostProvider()
     * @param Post $validPost
     */
    public function testCreatePost(Post $validPost): void
    {
        // Removing id from post array because before post is created id is not known
        unset($validPost['id']);

        // Mock the required repository and configure relevant method return value
        // Here I find ->expects() relevant since the test is about if the method is called or not
        // but should the expected parameter be tested as well? ->with($this->equalTo($validPost)) not included
        // because I dont want an annoying test function that fails for nothing if code changes. Didn't see the
        // real need for a test but maybe I'm wrong.
        $this->mock(PostRepository::class)->expects(self::once())->method('insertPost')->willReturn($postId);

        // Mock because it is used in the validation logic.
        $this->mock(UserExistenceCheckerRepository::class)->method('userExists')->willReturn(true);

        /** @var PostCreator $postCreator */
        $postCreator = $this->container->get(PostCreator::class);

        $postObj = new Post($validPost);

        self::assertEquals($postId, $postCreator->createPost($postObj));
    }

    /**
     * Test that no post is created when values are invalid.
     * validatePostCreationOrUpdate() will be tested separately but
     * here it is ensured that this validation is called in registerUser
     * but without specific error analysis. Important is that it didn't create it.
     * The method is called with each value of the provider
     *
     * @dataProvider \App\Test\Provider\PostProvider::invalidPostsProvider()
     * @param array $invalidPost
     */
    public function testCreatePost_invalid(array $invalidPost): void
    {
        // Mock because it is used by the validation logic.
        // Empty mock would do the trick as well as it would just return null on non defined functions.
        // A post is linked to an user in all cases so user has to exist. What happens if user doesn't exist
        // will be tested in a different function otherwise this test would always fail and other invalid
        // values would not be noticed
        $this->mock(UserExistenceCheckerRepository::class)->method('userExists')->willReturn(true);

        /** @var PostCreator $service */
        $service = $this->container->get(PostCreator::class);

        $this->expectException(ValidationException::class);

        $service->createPost(new Post($invalidPost));
        // If we wanted to test more detailed, the error messages could be tested, that the right message(s) appear
    }

    /**
     * Test createPost when user doesn't exist
     *
     * @dataProvider \App\Test\Provider\PostProvider::onePostProvider()
     * @param array $validPost
     */
    public function testCreatePost_notExistingUser(array $validPost): void
    {
        // Point of this test is not existing user
        $this->mock(UserExistenceCheckerRepository::class)->method('userExists')->willReturn(false);

        /** @var PostCreator $service */
        $service = $this->container->get(PostCreator::class);

        $this->expectException(ValidationException::class);

        $service->createPost(new Post($validPost));
    }
}
