<?php

namespace App\Test\Unit\Domain\User;

use App\Domain\Auth\AuthService;
use App\Domain\Exceptions\ValidationException;
use App\Domain\Security\SecurityService;
use App\Domain\User\User;
use App\Domain\User\UserService;
use App\Domain\Utility\EmailService;
use App\Infrastructure\Post\PostRepository;
use App\Infrastructure\User\UserRepository;
use App\Infrastructure\User\UserVerificationRepository;
use App\Test\AppTestTrait;
use PHPUnit\Framework\TestCase;

class UserServiceTest extends TestCase
{
    use AppTestTrait;

    /**
     * Test findAllUsers() from UserService
     *
     * @dataProvider \App\Test\Provider\UserProvider::oneSetOfMultipleUsersProvider()
     * @param array $users
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
     * Test findUser() from UserService
     *
     * @dataProvider \App\Test\Provider\UserProvider::oneUserProvider()
     * @param array $user
     */
    public function testFindUser(array $user): void
    {
        // Mock the required repository and configure relevant method return value
        $this->mock(UserRepository::class)->method('findUserById')->willReturn($user);

        // Instantiate autowired UserService which uses the function from the previously defined custom mock
        /** @var UserService $service */
        $service = $this->container->get(UserService::class);

        self::assertEquals($user, $service->findUser($user['id']));
    }

    /**
     * Test findUserByEmail() from UserService
     *
     * @dataProvider \App\Test\Provider\UserProvider::oneUserProvider()
     * @param array $user
     */
    public function testFindUserByEmail(array $user): void
    {
        // Mock the required repository and configure relevant method return value
        $this->mock(UserRepository::class)->method('findUserByEmail')->willReturn($user);

        // Instantiate autowired UserService which uses the function from the previously defined custom mock
        /** @var UserService $service */
        $service = $this->container->get(UserService::class);

        self::assertEquals($user, $service->findUserByEmail($user['email']));
    }

    /**
     * Test registerUser() from UserService
     *
     * @dataProvider \App\Test\Provider\UserProvider::oneUserProvider()
     * @param array $validUser
     */
    public function testRegisterUser(array $validUser): void
    {
        // Return type of UserRepository:insertUser is string
        $userId = (int)$validUser['id'];

        // Removing id from user because before user is created; id is not known
        unset($validUser['id']);

        // Mock the required repository and configure relevant method return value
        $this->mock(UserRepository::class)->method('insertUser')->willReturn($userId);
        // findUserByEmail automatically returns null as class is mocked and no return value is set

        $this->mock(SecurityService::class);
        $this->mock(UserVerificationRepository::class);
        $this->mock(EmailService::class);

        // Instantiate autowired UserService which uses the function from the previously defined custom mock
        /** @var AuthService $service */
        $service = $this->container->get(AuthService::class);

        // Create an user object
        $userObj = new User($validUser);

        self::assertEquals($userId, $service->registerUser($userObj));
    }

    /**
     * Test createUser() with invalid values
     * Test that no user is created when values are invalid
     * validateUserRegistration() will be tested separately but
     * here we ensure that this validation is going on in createUser
     * but without specific error analysis. Only that it didn't create it.
     * The method is called with each value of the provider
     *
     * @dataProvider \App\Test\Provider\UserProvider::invalidUserProvider()
     * @param array $invalidUser
     */
    public function testCreateUser_invalid(array $invalidUser): void
    {
        // Mock UserRepository because it is used by the validation logic.
        // Empty mock would do the trick as well it would just return null on non defined functions.
        // If findUserByEmail returns null, validation thinks the user doesn't exist which has to be the case
        // when creating a new user.
        $this->mock(UserRepository::class)->method('findUserByEmail')->willReturn(null);
        // todo in validation testing do a specific unit test to test the behaviour when email already exists
        $this->mock(SecurityService::class);
        $this->mock(UserVerificationRepository::class);
        $this->mock(EmailService::class);

        /** @var AuthService $service */
        $service = $this->container->get(AuthService::class);

        $this->expectException(ValidationException::class);

        $service->registerUser(new User($invalidUser));
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
        $userRepositoryMock->method('updateUser')->willReturn(true);
        // Used in Validation to check user existence
        $userRepositoryMock->method('userExists')->willReturn(true);

        /** @var UserService $service */
        $service = $this->container->get(UserService::class);

        self::assertTrue($service->updateUser(new User($validUser)));
    }

    /**
     * Test updateUser() with invalid users
     * Test that data from existing user is validated before being updated
     *
     * @dataProvider \App\Test\Provider\UserProvider::invalidUserProvider()
     * @param array $invalidUser
     */
    public function testUpdateUser_invalid(array $invalidUser): void
    {
        // Mock UserRepository because it is used by the validation logic
        // In this test user exists so every invalid data from invalidUserProvider() can throw
        // its error. Otherwise there would be always the error because of the exist and each data
        // could not be tested
        $this->mock(UserRepository::class)->method('userExists')->willReturn(true);

        /** @var UserService $service */
        $service = $this->container->get(UserService::class);

        $this->expectException(ValidationException::class);

        $service->updateUser(new User($invalidUser));
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

        /** @var UserService $service */
        $service = $this->container->get(UserService::class);

        $this->expectException(ValidationException::class);

        $service->updateUser(new User($validUser));
    }

    /**
     * Test deleteUser()
     * Since in this function not much logic is going on
     * I test if the repo method to delete all posts related
     * to the user is called and the method to delete the user itself
     */
    public function testDeleteUser(): void
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
            ->method('deleteUser')
            ->with(self::equalTo($userId))
            ->willReturn(true);

        // Instantiate autowired UserService which uses the function from the previously defined custom mock
        /** @var UserService $service */
        $service = $this->container->get(UserService::class);

        self::assertTrue($service->deleteUser($userId));
    }
}


