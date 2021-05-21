<?php

namespace App\Test\Unit\Domain\User;

use App\Domain\User\Service\UserDeleter;
use App\Infrastructure\Post\PostDeleterRepository;
use App\Infrastructure\User\UserDeleterRepository;
use App\Test\AppTestTrait;
use PHPUnit\Framework\TestCase;

class UserDeleterTest extends TestCase
{
    use AppTestTrait;

    /**
     * Test deleteUser()
     * Since in this function not much logic is going on
     * I test if the repo method to delete all posts related
     * to the user is called and the method to delete the user itself
     */
    public function testDeleteUserById(): void
    {
        $userId = 1;
        // Mock user repository and post repository
        $this->mock(PostDeleterRepository::class)
            ->expects(self::once())
            ->method('deletePostsFromUser')
            // With parameter user id
            ->with(self::equalTo($userId))
            ->willReturn(true);

        $this->mock(UserDeleterRepository::class)
            ->expects(self::once())
            ->method('deleteUserById')
            ->with(self::equalTo($userId))
            ->willReturn(true);

        // Instantiate autowired UserService which uses the function from the previously defined custom mock
        /** @var UserDeleter $service */
        $service = $this->container->get(UserDeleter::class);

        self::assertTrue($service->deleteUser($userId));
    }
}


