<?php

namespace App\Test\Unit\User;

use App\Domain\User\Data\UserData;
use App\Domain\User\Service\UserFinder;
use App\Infrastructure\User\UserFinderRepository;
use App\Test\Traits\AppTestTrait;
use PHPUnit\Framework\TestCase;

class UserFinderTest extends TestCase
{
    use AppTestTrait;

    /**
     * Test findAllUsers() from UserFinder
     *
     * @dataProvider \App\Test\Provider\User\UserDataProvider::oneSetOfMultipleUserObjectsProvider()
     * @param UserData[] $users
     */
    public function testFindAllUsers(array $users): void
    {
        // Add mock class to container and define return value for method findAllPostsWithUsers so the service can use it
        $this->mock(UserFinderRepository::class)->method('findAllUsers')->willReturn($users);

        // Here we don't need to specify what the function will do / return since its exactly that
        // which is being tested. So we can take the autowired class instance from the container directly.
        $service = $this->container->get(UserFinder::class);

        self::assertEquals($users, $service->findAllUsers());
    }

    /**
     * Test findUserById() from UserFinder
     *
     * @dataProvider \App\Test\Provider\User\UserDataProvider::oneUserObjectProvider()
     * @param UserData $user
     */
    public function testFindUserById(UserData $user): void
    {
        // In PHP different variables can point to the same object pointer.
        // This means that if I dont do the clone below, the assertEquals has no point as whatever transformation
        // the user object makes in the service, $user var will be changed too even when using different var names.
        // Real example: findUserById() removes password hash by default so if I compare the object from the provider
        // (containing the password hash) with the one returned by findUserById() the test should fail but it doesn't as
        // the $user object is changed as well
        $user2 = clone $user;
        // Mock the required repository and configure relevant method return value
        $this->mock(UserFinderRepository::class)->method('findUserById')->willReturn($user2);

        // Instantiate autowired UserFinder which uses the function from the previously defined custom mock
        /** @var UserFinder $service */
        $service = $this->container->get(UserFinder::class);

        // Test case 1: findUserById with password hash
        $userWithPasswordHash = $service->findUserById($user->id, true);
        // Important to assert here, before calling findUserById() without password hash as it removes the value
        // of $userWithPasswordHash too as it points to the same object
        self::assertEquals($user, $userWithPasswordHash);

        // Test case 2: findUserById without password hash
        $userWithoutPasswordHash = $service->findUserById($user->id);
        // Remove password hash from source object as its done too in findUserById() if the second argument is null
        $user->passwordHash = null;
        // Important to compare against $user as $user2 does the same changes than $user3 bcs the variables have the same object pointer
        self::assertEquals($user, $userWithoutPasswordHash);
    }

    /**
     * Test findUserByEmail() from UserFinder
     *
     * @dataProvider \App\Test\Provider\User\UserDataProvider::oneUserObjectProvider()
     * @param UserData $user
     */
    public function testFindUserByEmail(UserData $user): void
    {
        // Mock the required repository and configure relevant method return value
        $this->mock(UserFinderRepository::class)->method('findUserByEmail')->willReturn($user);

        // Instantiate autowired UserFinder which uses the function from the previously defined custom mock
        /** @var UserFinder $service */
        $service = $this->container->get(UserFinder::class);

        self::assertEquals($user, $service->findUserByEmail($user->email));
    }
}


