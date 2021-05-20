<?php

namespace App\Test\Unit\Domain\Auth;

use App\Domain\Auth\Service\LoginVerifier;
use App\Domain\Exceptions\InvalidCredentialsException;
use App\Domain\Exceptions\ValidationException;
use App\Domain\Security\Service\SecurityLoginChecker;
use App\Domain\User\DTO\User;
use App\Domain\User\Service\UserFinder;
use App\Infrastructure\User\UserRepository;
use App\Test\AppTestTrait;
use PHPUnit\Framework\TestCase;

class LoginVerifierTest extends TestCase
{
    use AppTestTrait;

    /**
     * Test getUserIdIfAllowedToLogin()
     *
     * @dataProvider \App\Test\Provider\UserProvider::oneUserObjectAndClientDataProvider()
     * @param array $validUserData
     * @param User $repoUser
     */
    public function testGetUserIdIfAllowedToLogin(array $validUserData, User $repoUser): void
    {
        $this->mock(UserRepository::class)->method('findUserByEmail')->willReturn($repoUser);
        $this->mock(SecurityLoginChecker::class); // Return null on security checks

        /** @var LoginVerifier $loginVerifier */
        $loginVerifier = $this->container->get(LoginVerifier::class);

        self::assertEquals($repoUser->id, $loginVerifier->GetUserIdIfAllowedToLogin($validUserData));
    }

    /**
     * Test getUserIdIfAllowedToLogin() with invalid user data
     *
     * @dataProvider \App\Test\Provider\UserProvider::invalidEmailAndPasswordsUsersProvider()
     * @param array $invalidUser
     */
    public function testGetUserIdIfAllowedToLogin_invalidData(array $invalidUser): void
    {
        // In case validationException is not thrown
        $this->mock(UserRepository::class); // Not relevant what findUserByEmail returns
        $this->mock(SecurityLoginChecker::class); // Return null on security checks

        /** @var LoginVerifier $loginVerifier */
        $loginVerifier = $this->container->get(LoginVerifier::class);

        $this->expectException(ValidationException::class);

        $loginVerifier->GetUserIdIfAllowedToLogin($invalidUser);
    }

    /**
     * Test getUserIdIfAllowedToLogin() with not existing user
     *
     * @dataProvider \App\Test\Provider\UserProvider::oneUserProvider()
     * @param array $validUser
     */
    public function testGetUserIdIfAllowedToLogin_userNotExisting(array $validUser): void
    {
        $this->mock(UserFinder::class)->method('findUserByEmail')->willReturn(new User());
        $this->mock(SecurityLoginChecker::class); // Return null on security checks

        /** @var LoginVerifier $loginVerifier */
        $loginVerifier = $this->container->get(LoginVerifier::class);

        $this->expectException(InvalidCredentialsException::class);

        $loginVerifier->GetUserIdIfAllowedToLogin($validUser);
    }

    /**
     * Test getUserIdIfAllowedToLogin() with invalid password
     * important to test this method extensively for security
     *
     * @dataProvider \App\Test\Provider\UserProvider::oneUserObjectAndClientDataProvider()
     * @param array $userData values from client
     * @param User $userObj values from repository
     */
    public function testGetUserIdIfAllowedToLogin_invalidPass(array $userData, User $userObj): void
    {
        // Add DIFFERENT password hash
        $userData['password_hash'] = password_hash('differentPass', PASSWORD_DEFAULT);

        // findUserByEmail() used in $loginVerifier->GetUserIdIfAllowedToLogin()
        $this->mock(UserFinder::class)->method('findUserByEmail')->willReturn($userObj);
        $this->mock(SecurityLoginChecker::class); // Return null on security checks

        /** @var LoginVerifier $loginVerifier */
        $loginVerifier = $this->container->get(LoginVerifier::class);

        $this->expectException(InvalidCredentialsException::class);

        $loginVerifier->GetUserIdIfAllowedToLogin($userData);
    }
}
