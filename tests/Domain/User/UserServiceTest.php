<?php

namespace App\Test\Domain\User;

use App\Domain\Exceptions\ValidationException;
use App\Domain\Post\Post;
use App\Domain\User\User;
use App\Domain\User\UserService;
use App\Domain\User\UserValidation;
use App\Domain\Utility\ArrayReader;
use App\Domain\Validation\ValidationResult;
use App\Infrastructure\Post\PostRepository;
use App\Infrastructure\User\UserRepository;
use App\Test\UnitTestUtil;
use Cake\Datasource\RepositoryInterface;
use PHPUnit\Framework\TestCase;
use Slim\Logger;

class UserServiceTest extends TestCase
{
    use UnitTestUtil;

    /**
     * Test function findAllUsers from UserService
     *
     * @dataProvider \App\Test\Domain\User\UserProvider::oneSetOfMultipleUsersProvider()
     * @param array $users
     */
    public function testFindAllUsers(array $users)
    {
        // Add mock class to container and define return value for method findAllPosts so the service can use it
        $this->mock(UserRepository::class)->method('findAllUsers')->willReturn($users);

        // Here we don't need to specify what the function will do / return since its exactly that
        // which is being tested. So we can take the autowired class instance from the container directly.
        $service = $this->container->get(UserService::class);

        self::assertEquals($users, $service->findAllUsers());
    }

    /**
     * @dataProvider \App\Test\Domain\User\UserProvider::oneUserProvider()
     * @param array $user
     */
    public function testFindUser(array $user)
    {
        // Mock the required repository and configure relevant method return value
        $this->mock(UserRepository::class)->method('findUserById')->willReturn($user);

        // Instantiate autowired UserService which uses the function from the previously defined custom mock
        /** @var UserService $service */
        $service = $this->container->get(UserService::class);

        self::assertEquals($user, $service->findUser($user['id']));
    }

    /**
     * @dataProvider \App\Test\Domain\User\UserProvider::oneUserProvider()
     * @param array $user
     */
    public function testFindUserByEmail(array $user)
    {
        // Mock the required repository and configure relevant method return value
        $this->mock(UserRepository::class)->method('findUserByEmail')->willReturn($user);

        // Instantiate autowired UserService which uses the function from the previously defined custom mock
        /** @var UserService $service */
        $service = $this->container->get(UserService::class);

        self::assertEquals($user, $service->findUserByEmail($user['email']));
    }

    /**
     * @dataProvider \App\Test\Domain\User\UserProvider::oneUserProvider()
     * @param array $validUser
     */
    public function testCreateUser(array $validUser)
    {
        // Return type of UserRepository:insertUser is string
        $userId = (string)$validUser['id'];

        // Removing id from user because before user is created id is not known
        unset($validUser['id']);

        // Mock the required repository and configure relevant method return value
        $this->mock(UserRepository::class)->method('insertUser')->willReturn($userId);

        // Instantiate autowired UserService which uses the function from the previously defined custom mock
        /** @var UserService $service */
        $service = $this->container->get(UserService::class);

        // Create an user object
        $userObj = new User(new ArrayReader($validUser));

        self::assertEquals($userId, $service->createUser($userObj));
    }

    /**
     * Test that no user is created when values are invalid
     * validateUserRegistration() will be tested separately but
     * here we ensure that this validation is going on in createUser
     * but without specific error analysis. Only that it didn't create it.
     * The method is called with each value of the provider
     *
     * @dataProvider \App\Test\Domain\User\UserProvider::invalidUsersProvider()
     * @param array $invalidUser
     */
    public function testInvalidCreateUser(array $invalidUser)
    {
        // Mock UserRepository because it is used by the validation logic.
        // Empty mock would do the trick as well it would just return null on non defined functions.
        // If findUserByEmail returns null, validation thinks the user doesn't exist which has to be the case
        // when creating a new user.
        $this->mock(UserRepository::class)->method('findUserByEmail')->willReturn(null);
        // todo in validation testing do a specific unit test to test the behaviour when email already exists

        /** @var UserService $service */
        $service = $this->container->get(UserService::class);

        $this->expectException(ValidationException::class);

        $service->createUser(new User(new ArrayReader($invalidUser)));
    }

    /**
     * @dataProvider \App\Test\Domain\User\UserProvider::oneUserProvider()
     * @param array $validUser
     */
    public function testUpdateUser(array $validUser)
    {
        $userRepositoryMock = $this->mock(UserRepository::class);
        $userRepositoryMock->method('updateUser')->willReturn(true);
        // Used in Validation to check user existence
        $userRepositoryMock->method('userExists')->willReturn(true);

        /** @var UserService $service */
        $service = $this->container->get(UserService::class);

        self::assertTrue($service->updateUser(new User(new ArrayReader($validUser))));
    }

    /**
     * Test that data from existing user is validated before being updated
     * (updateUser)
     *
     * @dataProvider \App\Test\Domain\User\UserProvider::invalidUsersProvider()
     * @param array $invalidUser
     */
    public function testInvalidUpdateUser(array $invalidUser)
    {
        // Mock UserRepository because it is used by the validation logic
        // In this test user exists so every invalid data from invalidUsersProvider() can throw
        // its error. Otherwise there would be always the error because of the exist and each data
        // could not be tested
        $this->mock(UserRepository::class)->method('userExists')->willReturn(true);

        /** @var UserService $service */
        $service = $this->container->get(UserService::class);

        $this->expectException(ValidationException::class);

        $service->updateUser(new User(new ArrayReader($invalidUser)));
    }

    /**
     * Test updateUser when user doesn't exist
     * (updateUser)
     *
     * @dataProvider \App\Test\Domain\User\UserProvider::oneUserProvider()
     * @param array $validUser
     */
    public function testNotExistingUpdateUser(array $validUser)
    {
        // Mock UserRepository because it is used by the validation logic
        // Point of this test is not existing user
        $this->mock(UserRepository::class)->method('userExists')->willReturn(false);

        /** @var UserService $service */
        $service = $this->container->get(UserService::class);

        $this->expectException(ValidationException::class);

        $service->updateUser(new User(new ArrayReader($validUser)));
    }

    /**
     * Since in this function not much logic is going on
     * I test if the repo method to delete all posts related
     * to the user is called and the method to delete the user itself
     */
    public function testDeleteUser()
    {
        $userId = 1;
        // Mock user repository and post repository
        $this->mock(PostRepository::class)
            ->expects($this->once())
            ->method('deletePostsFromUser')
            // With parameter user id
            ->with($this->equalTo($userId))
            ->willReturn(true);

        $this->mock(UserRepository::class)
            ->expects($this->once())
            ->method('deleteUser')
            ->with($this->equalTo($userId))
            ->willReturn(true);

        // Instantiate autowired UserService which uses the function from the previously defined custom mock
        /** @var UserService $service */
        $service = $this->container->get(UserService::class);

        self::assertTrue($service->deleteUser($userId));
    }
}


