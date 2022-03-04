<?php

namespace App\Test\Unit\Post;

use App\Domain\Exceptions\ForbiddenException;
use App\Domain\Post\Data\PostData;
use App\Domain\Post\Service\PostFinder;
use App\Domain\Post\Service\PostUpdater;
use App\Infrastructure\Authentication\UserRoleFinderRepository;
use App\Infrastructure\Post\PostUpdaterRepository;
use App\Test\Traits\AppTestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Post update unit test covered here:
 * - normal update
 * - edit other post as user (403 Forbidden)
 * - edit other post as admin
 * NOT in this test (not useful enough to me):
 * - edit non-existing post as admin (expected return value false)
 * - edit non-existing post as user (expected forbidden exception)
 * - make edit request but with the same content as before (expected not updated response)
 */
class PostUpdaterTest extends TestCase
{
    use AppTestTrait;

    /**
     * Test that service method updatePost() calls PostUpdaterRepository:updatePost()
     * and that (service) updatePost() returns the bool true returned by repo
     *
     * Invalid or not existing user don't have to be tested since it's the same
     * validation as registerUser() and it's already done there
     */
    public function testUpdatePost(): void
    {
        $userRole = 'user';
        $postId = 1;
        $postUserId = 1;
        $loggedInUserId = 1;
        $valuesToChange = ['message' => 'This is a new message content.'];

        // Post from db used to check ownership
        $postFromDb = new PostData(['id' => $postId, 'user_id' => $postUserId, 'message' => 'Test message.']);
        $this->mock(PostFinder::class)->method('findPost')->willReturn($postFromDb);

        // User role 'user'
        $this->mock(UserRoleFinderRepository::class)->method('getUserRoleById')->willReturn($userRole);

        // With ->expects() to assert that the method is called
        $this->mock(PostUpdaterRepository::class)->expects(self::once())->method('updatePost')->willReturn(true);

        /** @var PostUpdater $service */
        $service = $this->container->get(PostUpdater::class);

        self::assertTrue($service->updatePost($postId, $valuesToChange, $loggedInUserId));
    }

    /**
     * Test that user cannot edit post attached to other user
     */
    public function testUpdatePost_otherPostAsUser(): void
    {
        $userRole = 'user';
        $postId = 1;
        $postUserId = 2; // Different from logged-in
        $loggedInUserId = 1;
        $valuesToChange = ['message' => 'This is a new message content.'];

        // Post from db used to check ownership
        $postFromDb = new PostData(['id' => $postId, 'user_id' => $postUserId, 'message' => 'Test message.']);
        $this->mock(PostFinder::class)->method('findPost')->willReturn($postFromDb);

        // User role 'user'
        $this->mock(UserRoleFinderRepository::class)->method('getUserRoleById')->willReturn($userRole);

        // Assert that updatePost() is NOT called
        $this->mock(PostUpdaterRepository::class)->expects(self::never())->method('updatePost');

        /** @var PostUpdater $service */
        $service = $this->container->get(PostUpdater::class);

        $this->expectException(ForbiddenException::class);

        $service->updatePost($postId, $valuesToChange, $loggedInUserId);
    }

    /**
     * Test that admin CAN edit post attached to other user
     */
    public function testUpdatePost_otherPostAsAdmin(): void
    {
        $userRole = 'admin';
        $postId = 1;
        $postUserId = 2; // Different from logged-in user
        $loggedInUserId = 1;
        $valuesToChange = ['message' => 'This is a new message content.'];

        // Post from db used to check ownership
        $postFromDb = new PostData(['id' => $postId, 'user_id' => $postUserId, 'message' => 'Test message.']);
        $this->mock(PostFinder::class)->method('findPost')->willReturn($postFromDb);

        // User role
        $this->mock(UserRoleFinderRepository::class)->method('getUserRoleById')->willReturn($userRole);

        // Assert that repo method updatePost() is called
        $this->mock(PostUpdaterRepository::class)->expects(self::once())->method('updatePost')->willReturn(true);

        /** @var PostUpdater $service */
        $service = $this->container->get(PostUpdater::class);

        self::assertTrue($service->updatePost($postId, $valuesToChange, $loggedInUserId));
    }

}
