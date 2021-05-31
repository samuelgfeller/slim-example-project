<?php

namespace App\Test\Unit\Domain\Authentication;

use App\Domain\Authentication\Service\UserRegisterer;
use App\Domain\Authentication\Service\VerificationTokenCreator;
use App\Domain\Exceptions\ValidationException;
use App\Domain\Security\Service\SecurityEmailChecker;
use App\Domain\User\DTO\User;
use App\Domain\Utility\Mailer;
use App\Infrastructure\Authentication\UserRegistererRepository;
use App\Infrastructure\Authentication\VerificationToken\VerificationTokenCreatorRepository;
use App\Infrastructure\Authentication\VerificationToken\VerificationTokenDeleterRepository;
use App\Infrastructure\Security\RequestCreatorRepository;
use App\Infrastructure\User\UserFinderRepository;
use App\Test\Traits\AppTestTrait;
use PHPUnit\Framework\TestCase;

class UserRegistererTest extends TestCase
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
        // Return type of UserFinderRepository:insertUser is string
        $userId = (int)$validUser['id'];

        // Removing id from user because before user is created; id is not known
        unset($validUser['id']);

        // Mock the required repository and configure relevant method return value
        $this->mock(UserRegistererRepository::class)->method('insertUser')->willReturn($userId);
        $this->mock(UserFinderRepository::class)->method('findUserByEmail')->willReturn(new User());

        $this->mock(SecurityEmailChecker::class)->expects(self::once())->method('performEmailAbuseCheck');
        $this->mock(VerificationTokenDeleterRepository::class)->expects(self::once())->method(
            'deleteVerificationToken'
        );
        $this->mock(VerificationTokenCreatorRepository::class)->expects(self::once())->method('insertUserVerification');
        $this->mock(Mailer::class)->expects(self::once())->method('setSubject')->with(
            'One more step to register'
        );

        // Instantiate autowired UserService which uses the function from the previously defined custom mock
        /** @var UserRegisterer $service */
        $service = $this->container->get(UserRegisterer::class);

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
        // Mock UserFinderRepository because it is used by the validation logic.
        // Empty mock would do the trick as well it would just return null on non defined functions.
        // If findUserByEmail returns null, validation thinks the user doesn't exist which has to be the case
        // when creating a new user.
        $this->mock(UserFinderRepository::class)->method('findUserByEmail')->willReturn(new User());
        // todo in validation testing do a specific unit test to test the behaviour when email already exists
        $this->mock(SecurityEmailChecker::class); // used in UserRegisterer
        // used in VerificationTokenCreator and UserAlreadyExistingHandler
        $this->mock(VerificationTokenDeleterRepository::class);
        $this->mock(VerificationTokenCreatorRepository::class); // used in VerificationTokenCreator
        $this->mock(RequestCreatorRepository::class); // used UserAlreadyExistingHandler and UserRegisterer
        $this->mock(Mailer::class); // used in VerificationTokenCreator

        /** @var UserRegisterer $service */
        $service = $this->container->get(UserRegisterer::class);

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
        $this->mock(UserFinderRepository::class)->method('findUserByEmail')->willReturn($existingUser);

        $this->mock(SecurityEmailChecker::class)->expects(self::once())->method('performEmailAbuseCheck');
        // Always called when inserting a new verification token which shouldn't be done in this function hence never()
        $this->mock(VerificationTokenDeleterRepository::class)->expects(self::never())->method(
            'deleteVerificationToken'
        );
        $this->mock(VerificationTokenCreatorRepository::class)->expects(self::never())->method(
            'insertUserVerification'
        );
        $this->mock(Mailer::class)->expects(self::once())->method('setSubject')->with(
            'Someone tried to create an account with your address'
        );

        // Instantiate autowired UserService which uses the function from the previously defined custom mock
        /** @var UserRegisterer $service */
        $service = $this->container->get(UserRegisterer::class);

        // registerUser returns false when user creation failed
        self::assertFalse($service->registerUser($userData));
    }

    /**
     * Test registerUser() from UserService with already existing user with same email
     *
     * @param array $userData values from client
     * @param User $existingUser values from repository
     * @throws \PHPMailer\PHPMailer\Exception
     *
     * @dataProvider \App\Test\Provider\UserProvider::oneUserObjectAndClientDataProvider()
     */
    public function testRegisterUser_existingUnverifiedUser(array $userData, User $existingUser): void
    {
        // Set user to active just to make sure
        $existingUser->status = User::STATUS_UNVERIFIED;

        // Set findUserByEmail to return user. That means that it already exists
        $this->mock(UserFinderRepository::class)->method('findUserByEmail')->willReturn($existingUser);
        // New user should be inserted and new insert id returned
        $this->mock(UserRegistererRepository::class)->expects(self::once())->method('insertUser')->willReturn(2);

        $this->mock(SecurityEmailChecker::class)->expects(self::once())->method('performEmailAbuseCheck');
        // Always called when inserting a new verification token
        $this->mock(VerificationTokenDeleterRepository::class)->expects(self::atLeastOnce())->method(
            'deleteVerificationToken'
        );
        $this->mock(VerificationTokenCreatorRepository::class)->expects(self::once())->method('insertUserVerification');
        $this->mock(Mailer::class)->expects(self::once())->method('setSubject')->with(
            'One more step to register'
        );

        // Instantiate autowired UserService which uses the function from the previously defined custom mock
        /** @var UserRegisterer $service */
        $service = $this->container->get(UserRegisterer::class);

        // registerUser returns false when user creation failed
        self::assertSame(2, $service->registerUser($userData));
    }


}
