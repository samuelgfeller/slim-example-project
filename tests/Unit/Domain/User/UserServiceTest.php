<?php

namespace App\Test\Unit\Domain\User;

use App\Domain\Exceptions\ValidationException;
use App\Domain\User\User;
use App\Domain\User\UserService;
use App\Infrastructure\Post\PostRepository;
use App\Infrastructure\User\UserRepository;
use App\Test\AppTestTrait;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\TestCase;

class UserServiceTest extends TestCase
{
    use AppTestTrait;

    /**
     * Test findAllUsers() from UserService
     *
     * @dataProvider \App\Test\Provider\UserProvider::oneSetOfMultipleUserObjectsProvider()
     * @param User[] $users
     */
    public function testFindAllUsers(array $users): void
    {
        // Add mock class to container and define return value for method findAllPosts so the service can use it
        $this->mock(UserRepository::class)->method('findAllUsers')->willReturn($users);

        // Here we don't need to specify what the function will do / return since its exactly that
        // which is being tested. So we can take the autowired class instance from the container directly.
        $service = $this->container->get(UserService::class);

        self::assertEquals($users, $service->findAllUsers());
    }

    /**
     * Test findUserById() from UserService
     *
     * @dataProvider \App\Test\Provider\UserProvider::oneUserObjectProvider()
     * @param User $user
     */
    public function testFindUserById(User $user): void
    {
        // Mock the required repository and configure relevant method return value
        $this->mock(UserRepository::class)->method('findUserById')->willReturn($user);

        // Instantiate autowired UserService which uses the function from the previously defined custom mock
        /** @var UserService $service */
        $service = $this->container->get(UserService::class);

        self::assertEquals($user, $service->findUserById($user->id));
    }

    /**
     * Test findUserByEmail() from UserService
     *
     * @dataProvider \App\Test\Provider\UserProvider::oneUserObjectProvider()
     * @param User $user
     */
    public function testFindUserByEmail(User $user): void
    {
        // Mock the required repository and configure relevant method return value
        $this->mock(UserRepository::class)->method('findUserByEmail')->willReturn($user);

        // Instantiate autowired UserService which uses the function from the previously defined custom mock
        /** @var UserService $service */
        $service = $this->container->get(UserService::class);

        self::assertEquals($user, $service->findUserByEmail($user->email));
    }

    /**
     * Test updateUser() from UserService
     *
     * @dataProvider \App\Test\Provider\UserProvider::oneUserProvider()
     * @param array $validUser
     */
    public function testUpdateUser(array $validUser): void
    {
        $userRepositoryMock = $this->mock(UserRepository::class);
        $userRepositoryMock->expects(self::once())->method('updateUser')->willReturn(true);
        // Used in Validation to check user existence
        $userRepositoryMock->method('userExists')->willReturn(true);

        $loggedInUserId = $this->container->get(SessionInterface::class)->get('user_id');
        $service = $this->container->get(UserService::class);

        self::assertTrue($service->updateUser($validUser['id'], $validUser, $loggedInUserId));
    }

    /**
     * Test updateUser() with invalid users
     * Test that data from existing user is validated before being updated
     *
     * @dataProvider \App\Test\Provider\UserProvider::invalidUserForUpdate()
     * @param array $invalidUser
     */
    public function testUpdateUser_invalid(array $invalidUser): void
    {
        // Mock UserRepository because it is used by the validation logic
        // In this test user exists so every invalid data from invalidUserProvider() can throw
        // its error. Otherwise there would be always the error because of the exist and each data
        // could not be tested
        $this->mock(UserRepository::class)->method('userExists')->willReturn(true);

        $loggedInUserId = $this->container->get(SessionInterface::class)->get('user_id');
        $service = $this->container->get(UserService::class);

        $this->expectException(ValidationException::class);

        $service->updateUser($invalidUser['id'], $invalidUser, $loggedInUserId);
    }

    /**
     * Test updateUser() when user doesn't exist
     *
     * @dataProvider \App\Test\Provider\UserProvider::oneUserProvider()
     * @param array $validUser
     */
    public function testUpdateUser_notExisting(array $validUser): void
    {
        // Mock UserRepository because it is used by the validation logic
        // Point of this test is not existing user
        $this->mock(UserRepository::class)->method('userExists')->willReturn(false);

        $loggedInUserId = $this->container->get(SessionInterface::class)->get('user_id');
        $service = $this->container->get(UserService::class);

        $this->expectException(ValidationException::class);

        $service->updateUser($validUser['id'], $validUser, $loggedInUserId);
    }

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
        $this->mock(PostRepository::class)
            ->expects(self::once())
            ->method('deletePostsFromUser')
            // With parameter user id
            ->with(self::equalTo($userId))
            ->willReturn(true);

        $this->mock(UserRepository::class)
            ->expects(self::once())
            ->method('deleteUserById')
            ->with(self::equalTo($userId))
            ->willReturn(true);

        // Instantiate autowired UserService which uses the function from the previously defined custom mock
        /** @var UserService $service */
        $service = $this->container->get(UserService::class);

        self::assertTrue($service->deleteUser($userId));
    }
}


