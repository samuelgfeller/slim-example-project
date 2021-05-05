<?php

namespace App\Test\Unit\Domain\Auth;

use App\Domain\Auth\AuthService;
use App\Domain\Exceptions\ValidationException;
use App\Domain\Security\SecurityService;
use App\Domain\User\User;
use App\Domain\Utility\EmailService;
use App\Infrastructure\User\UserRepository;
use App\Infrastructure\User\UserVerificationRepository;
use App\Test\AppTestTrait;
use PHPUnit\Framework\TestCase;

class AuthServiceRegisterTest extends TestCase
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
        $userRepo = $this->mock(UserRepository::class);
        $userRepo->method('insertUser')->willReturn($userId);
        $userRepo->method('findUserByEmail')->willReturn(new User());

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

        self::assertEquals($userId, $service->registerUser($validUser));
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
        $this->mock(UserRepository::class)->method('findUserByEmail')->willReturn(new User());
        // todo in validation testing do a specific unit test to test the behaviour when email already exists
        $this->mock(SecurityService::class);
        $this->mock(UserVerificationRepository::class);
        $this->mock(EmailService::class);

        /** @var AuthService $service */
        $service = $this->container->get(AuthService::class);

        $this->expectException(ValidationException::class);

        $service->registerUser($invalidUser);
    }

    /**
     * Test registerUser() from UserService with already existing user with same email
     *
     * @param array $userData values from client
     * @param User $existingUser values from repository
     * @throws \PHPMailer\PHPMailer\Exception
     * @todo test with different statuses of existing user
     *
     * @dataProvider \App\Test\Provider\UserProvider::oneUserObjectAndClientDataProvider()
     */
    public function testRegisterUser_existingActiveUser(array $userData, User $existingUser): void
    {
        // Set user to active just to make sure
        $existingUser->status = User::STATUS_ACTIVE;

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
        self::assertFalse($service->registerUser($userData));
    }
}