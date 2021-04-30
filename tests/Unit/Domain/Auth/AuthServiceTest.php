<?php

namespace App\Test\Unit\Domain\Auth;

use App\Domain\Auth\AuthService;
use App\Domain\Exceptions\InvalidCredentialsException;
use App\Domain\Exceptions\ValidationException;
use App\Domain\Security\SecurityService;
use App\Domain\User\User;
use App\Domain\User\UserService;
use App\Domain\Utility\EmailService;
use App\Infrastructure\User\UserRepository;
use App\Infrastructure\User\UserVerificationRepository;
use App\Test\AppTestTrait;
use phpDocumentor\Reflection\Types\Self_;
use PHPUnit\Framework\TestCase;

class AuthServiceTest extends TestCase
{
    use AppTestTrait;

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

        $this->mock(SecurityService::class)->expects(self::once())->method('performEmailAbuseCheck');
        $userVerificationRepositoryMock = $this->mock(UserVerificationRepository::class);
        $userVerificationRepositoryMock->expects(self::once())->method('deleteVerificationToken');
        $userVerificationRepositoryMock->expects(self::once())->method('insertUserVerification');
        $this->mock(EmailService::class)->expects(self::once())->method('setSubject')->with(
            'One more step to register'
        );

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
    public function testRegisterUser_invalid(array $invalidUser): void
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
     * Test registerUser() from UserService with already existing user with same email
     * @todo test with different statuses of existing user
     *
     * @dataProvider \App\Test\Provider\UserProvider::oneUserObjectProvider()
     * @param User $existingUser
     */
    public function testRegisterUser_existingActiveUser(User $existingUser): void
    {
        // Set user to active
        $existingUser->status = User::STATUS_ACTIVE;
        // Removing id from user because before user is created; id is not known
        $existingUser->id = null;

        // Set findUserByEmail to return user. That means that it already exists
        $this->mock(UserRepository::class)->method('findUserByEmail')->willReturn($existingUser);

        $this->mock(SecurityService::class)->expects(self::once())->method('performEmailAbuseCheck');
        $userVerificationRepositoryMock = $this->mock(UserVerificationRepository::class);
        $userVerificationRepositoryMock->expects(self::never())->method('deleteVerificationToken');
        $userVerificationRepositoryMock->expects(self::never())->method('insertUserVerification');
        $this->mock(EmailService::class)->expects(self::once())->method('setSubject')->with(
            'Someone tried to create an account with your address'
        );

        // Instantiate autowired UserService which uses the function from the previously defined custom mock
        /** @var AuthService $service */
        $service = $this->container->get(AuthService::class);

        // registerUser returns false when user creation failed
        self::assertFalse($service->registerUser($existingUser));
    }


    /**
     * Test getUserIdIfAllowedToLogin()
     *
     * @dataProvider \App\Test\Provider\UserProvider::oneUserProvider()
     * @param array $validUser
     */
    public function testGetUserIdIfAllowedToLogin(array $validUser): void
    {
        // findUserByEmail() used in $authService->GetUserIdIfAllowedToLogin()
        $this->mock(UserService::class)->method('findUserByEmail')->willReturn($validUser);
        $this->mock(SecurityService::class); // Return null on security checks

        /** @var AuthService $authService */
        $authService = $this->container->get(AuthService::class);

        self::assertEquals($validUser['id'], $authService->GetUserIdIfAllowedToLogin($validUser));
    }

    /**
     * Test getUserIdIfAllowedToLogin() with invalid user data
     *
     * @dataProvider \App\Test\Provider\UserProvider::invalidEmailAndPasswordsUsersProvider()
     * @param array $validUser
     */
    public function testGetUserIdIfAllowedToLogin_invalidData(array $validUser): void
    {
        // findUserByEmail() used in $authService->GetUserIdIfAllowedToLogin()
        $this->mock(UserService::class)->method('findUserByEmail')->willReturn($validUser);
        $this->mock(SecurityService::class); // Return null on security checks

        /** @var AuthService $authService */
        $authService = $this->container->get(AuthService::class);

        $this->expectException(ValidationException::class);

        $authService->GetUserIdIfAllowedToLogin($validUser);
    }

    /**
     * Test getUserIdIfAllowedToLogin() with not existing user
     *
     * @dataProvider \App\Test\Provider\UserProvider::oneUserProvider()
     * @param array $validUser
     */
    public function testGetUserIdIfAllowedToLogin_userNotExisting(array $validUser): void
    {
        // findUserByEmail() used in $authService->GetUserIdIfAllowedToLogin()
        $this->mock(UserService::class)->method('findUserByEmail')->willReturn(null);
        $this->mock(SecurityService::class); // Return null on security checks

        /** @var AuthService $authService */
        $authService = $this->container->get(AuthService::class);

        $this->expectException(InvalidCredentialsException::class);

        $authService->GetUserIdIfAllowedToLogin($validUser);
    }

    /**
     * Test getUserIdIfAllowedToLogin() with invalid password
     * important to test this method extensively for security
     *
     * @dataProvider \App\Test\Provider\UserProvider::oneUserProvider()
     * @param array $validUser
     */
    public function testGetUserIdIfAllowedToLogin_invalidPass(array $validUser): void
    {
        // Add DIFFERENT password hash
        $validUser['password_hash'] = password_hash($validUser['password'] . 'differentPass', PASSWORD_DEFAULT);

        // findUserByEmail() used in $authService->GetUserIdIfAllowedToLogin()
        $this->mock(UserService::class)->method('findUserByEmail')->willReturn($validUser);
        $this->mock(SecurityService::class); // Return null on security checks

        /** @var AuthService $authService */
        $authService = $this->container->get(AuthService::class);

        $this->expectException(InvalidCredentialsException::class);

        $authService->GetUserIdIfAllowedToLogin($validUser);
    }

    /**
     * Test getUserRoleById() with different roles
     *
     * Test with multiple users to have different roles
     * @dataProvider \App\Test\Provider\UserProvider::validUserProvider()
     * @param array $user
     */
    public function testGetUserRoleById(array $user): void
    {
        $this->mock(UserRepository::class)->method('getUserRole')->willReturn($user['role']);

        /** @var AuthService $authService */
        $authService = $this->container->get(AuthService::class);

        self::assertEquals($user['role'], $authService->getUserRoleById($user['id']));
    }
}
