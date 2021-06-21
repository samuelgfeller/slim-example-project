<?php

namespace App\Test\Unit\Post;

use App\Domain\Post\DTO\Post;
use App\Domain\Post\Service\PostUpdater;
use App\Infrastructure\Post\PostUpdaterRepository;
use App\Infrastructure\User\UserExistenceCheckerRepository;
use App\Test\Traits\AppTestTrait;
use PHPUnit\Framework\TestCase;

class PostUpdaterTest extends TestCase
{
    use AppTestTrait;

    /**
     * Test that service method updatePost() calls PostUpdaterRepository:updatePost()
     * and that (service) updatePost() returns the bool true returned by repo
     *
     * Invalid or not existing user don't have to be tested since it's the same
     * validation as registerUser() and it's already done there
     *
     * @dataProvider \App\Test\Provider\PostProvider::onePostProvider()
     * @param Post $validPost
     */
    public function testUpdatePost(Post $validPost): void
    {
        $this->mock(UserExistenceCheckerRepository::class)->method('userExists')->willReturn(true);

        // With ->expects() to test if the method is called
        $this->mock(PostUpdaterRepository::class)->expects(self::once())->method('updatePost')->willReturn(true);

        /** @var PostUpdater $service */
        $service = $this->container->get(PostUpdater::class);

        self::assertTrue($service->updatePost($validPost));
    }
}
