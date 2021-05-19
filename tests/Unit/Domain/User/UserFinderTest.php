<?php

namespace App\Test\Unit\Domain\User;

use App\Domain\User\DTO\User;
use App\Domain\User\Service\UserFinder;
use App\Infrastructure\User\UserRepository;
use App\Test\AppTestTrait;
use PHPUnit\Framework\TestCase;

class UserFinderTest extends TestCase
{
    use AppTestTrait;

    /**
     * Test findAllUsers() from UserFinder
     *
     * @dataProvider \App\Test\Provider\UserProvider::oneSetOfMultipleUserObjectsProvider()
     * @param User[] $users
     */
    public function testFindAllUsers(array $users): void
    {
        // Add mock class to container and define return value for method findAllPostsWithUsers so the service can use it
        $this->mock(UserRepository::class)->method('findAllUsers')->willReturn($users);

        // Here we don't need to specify what the function will do / return since its exactly that
        // which is being tested. So we can take the autowired class instance from the container directly.
        $service = $this->container->get(UserFinder::class);

        self::assertEquals($users, $service->findAllUsers());
    }

    /**
     * Test findUserById() from UserFinder
     *
     * @dataProvider \App\Test\Provider\UserProvider::oneUserObjectProvider()
     * @param User $user
     */
    public function testFindUserById(User $user): void
    {
        // Mock the required repository and configure relevant method return value
        $this->mock(UserRepository::class)->method('findUserById')->willReturn($user);

        // Instantiate autowired UserFinder which uses the function from the previously defined custom mock
        /** @var UserFinder $service */
        $service = $this->container->get(UserFinder::class);

        self::assertEquals($user, $service->findUserById($user->id));
    }

    /**
     * Test findUserByEmail() from UserFinder
     *
     * @dataProvider \App\Test\Provider\UserProvider::oneUserObjectProvider()
     * @param User $user
     */
    public function testFindUserByEmail(User $user): void
    {
        // Mock the required repository and configure relevant method return value
        $this->mock(UserRepository::class)->method('findUserByEmail')->willReturn($user);

        // Instantiate autowired UserFinder which uses the function from the previously defined custom mock
        /** @var UserFinder $service */
        $service = $this->container->get(UserFinder::class);

        self::assertEquals($user, $service->findUserByEmail($user->email));
    }
}


