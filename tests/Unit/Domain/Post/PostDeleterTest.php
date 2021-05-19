<?php

namespace App\Test\Unit\Domain\Post;

use App\Domain\Post\Service\PostDeleter;
use App\Infrastructure\Post\PostRepository;
use App\Test\AppTestTrait;
use PHPUnit\Framework\TestCase;

class PostDeleterTest extends TestCase
{
    use AppTestTrait;

    /**
     * Test that postRepository:deletePost() is called in
     * post service
     */
    public function testDeletePost(): void
    {
        $postId = 1;

        $this->mock(PostRepository::class)
            ->expects(self::once())
            ->method('deletePost')
            // With parameter user id
            ->with(self::equalTo($postId))
            ->willReturn(true);

        /** @var PostDeleter $service */
        $service = $this->container->get(PostDeleter::class);

        self::assertTrue($service->deletePost($postId));
    }
}
