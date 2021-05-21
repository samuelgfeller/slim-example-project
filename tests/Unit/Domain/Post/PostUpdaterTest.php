<?php

namespace App\Test\Unit\Domain\Post;

use App\Domain\Post\DTO\Post;
use App\Domain\Post\Service\PostUpdater;
use App\Infrastructure\Post\PostRepository;
use App\Infrastructure\User\UserExistenceCheckerRepository;
use App\Test\AppTestTrait;
use PHPUnit\Framework\TestCase;

class PostUpdaterTest extends TestCase
{
    use AppTestTrait;

    /**
     * Test that service method updatePost() calls PostRepository:updatePost()
     * and that (service) updatePost() returns the bool true returned by repo
     *
     * Invalid or not existing user don't have to be tested since it's the same
     * validation as registerUser() and it's already done there
     *
     * @dataProvider \App\Test\Provider\PostProvider::onePostProvider()
     * @param array $validPost
     */
    public function testUpdatePost(array $validPost): void
    {
        $this->mock(UserExistenceCheckerRepository::class)->method('userExists')->willReturn(true);

        // With ->expects() to test if the method is called
        $this->mock(PostRepository::class)->expects(self::once())->method('updatePost')->willReturn(true);

        /** @var PostUpdater $service */
        $service = $this->container->get(PostUpdater::class);

        self::assertTrue($service->updatePost(new Post($validPost)));
    }
}
