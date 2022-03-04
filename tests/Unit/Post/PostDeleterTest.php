<?php

namespace App\Test\Unit\Post;

use App\Domain\Exceptions\ForbiddenException;
use App\Domain\Post\Data\PostData;
use App\Domain\Post\Service\PostDeleter;
use App\Domain\Post\Service\PostFinder;
use App\Infrastructure\Authentication\UserRoleFinderRepository;
use App\Infrastructure\Post\PostDeleterRepository;
use App\Test\Traits\AppTestTrait;
use PHPUnit\Framework\TestCase;

class PostDeleterTest extends TestCase
{
    use AppTestTrait;

    /**
     * Test that PostDeleterRepository:deletePost() is called in post service when
     * user tried to delete its own post
     */
    public function testDeletePost_ownPost(): void
    {
        $postId = 1;
        // Logged-in user same than user_id of post
        $postUserId = 1;
        $loggedInUserId = 1;
        $userRole = 'user';

        // User role 'user'
        $this->mock(UserRoleFinderRepository::class)->method('getUserRoleById')->willReturn($userRole);

        // Post from db used to check ownership
        $postFromDb = new PostData(['id' => 1, 'user_id' => $postUserId, 'message' => 'Test message.']);
        $this->mock(PostFinder::class)->method('findPost')->willReturn($postFromDb);

        $this->mock(PostDeleterRepository::class)
            ->expects(self::once())
            ->method('deletePost')
            // With parameter post id
            ->with(self::equalTo($postId))
            ->willReturn(true);

        /** @var PostDeleter $service */
        $service = $this->container->get(PostDeleter::class);

        self::assertTrue($service->deletePost($postId, $loggedInUserId));
    }

    /**
     * Test that ForbiddenException is thrown when user tries to delete post
     * that he doesn't own.
     */
    public function testDeletePost_userNotOwner(): void
    {
        $postId = 1;
        $postUserId = 1;
        // Logged-in user different from user_id of post
        $loggedInUserId = 2;
        $userRole = 'user';

        // User role 'user'
        $this->mock(UserRoleFinderRepository::class)->method('getUserRoleById')->willReturn($userRole);

        // Post from db used to check ownership which WILL NOT correspond
        $postFromDb = new PostData(['id' => 1, 'user_id' => $postUserId, 'message' => 'Test message.']);
        $this->mock(PostFinder::class)->method('findPost')->willReturn($postFromDb);

        // deletePost should NEVER be called
        $this->mock(PostDeleterRepository::class)->expects(self::never())->method('deletePost');

        /** @var PostDeleter $service */
        $service = $this->container->get(PostDeleter::class);

        $this->expectException(ForbiddenException::class);

        self::assertTrue($service->deletePost($postId, $loggedInUserId));
    }


    /**
     * Test that admin can delete post that he doesn't own
     */
    public function testDeletePost_otherPostAsAdmin(): void
    {
        $postId = 1;
        // Logged-in user DIFFERENT from user_id of post
        $postUserId = 1;
        $loggedInUserId = 2;
        $userRole = 'admin';

        // User role
        $this->mock(UserRoleFinderRepository::class)->method('getUserRoleById')->willReturn($userRole);

        // Post from db used to check ownership
        $postFromDb = new PostData(['id' => 1, 'user_id' => $postUserId, 'message' => 'Test message.']);
        $this->mock(PostFinder::class)->method('findPost')->willReturn($postFromDb);

        $this->mock(PostDeleterRepository::class)
            ->expects(self::once())
            ->method('deletePost')
            // With parameter post id
            ->with(self::equalTo($postId))
            ->willReturn(true);

        /** @var PostDeleter $service */
        $service = $this->container->get(PostDeleter::class);

        self::assertTrue($service->deletePost($postId, $loggedInUserId));
    }

}
