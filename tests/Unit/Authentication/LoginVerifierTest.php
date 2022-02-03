<?php

namespace App\Test\Unit\Authentication;

use App\Domain\Authentication\Service\LoginVerifier;
use App\Domain\Exceptions\InvalidCredentialsException;
use App\Domain\Exceptions\ValidationException;
use App\Domain\Security\Service\SecurityLoginChecker;
use App\Domain\User\Data\UserData;
use App\Domain\User\Service\UserFinder;
use App\Infrastructure\User\UserFinderRepository;
use App\Test\Traits\AppTestTrait;
use PHPUnit\Framework\TestCase;

class LoginVerifierTest extends TestCase
{
    use AppTestTrait;

    /**
     * Test getUserIdIfAllowedToLogin()
     *
     * @dataProvider \App\Test\Provider\User\UserDataProvider::oneUserObjectAndClientDataProvider()
     * @param array $validUserData
     * @param UserData $repoUser
     */
    public function testGetUserIdIfAllowedToLogin(array $validUserData, UserData $repoUser): void
    {
        $this->mock(UserFinderRepository::class)->method('findUserByEmail')->willReturn($repoUser);
        $this->mock(SecurityLoginChecker::class); // Return null on security checks

        /** @var LoginVerifier $loginVerifier */
        $loginVerifier = $this->container->get(LoginVerifier::class);

        self::assertEquals($repoUser->id, $loginVerifier->GetUserIdIfAllowedToLogin($validUserData));
    }

    /**
     * Test getUserIdIfAllowedToLogin() with invalid user data
     *
     * @dataProvider \App\Test\Provider\User\UserDataProvider::invalidEmailAndPasswordsUsersProvider()
     * @param array $invalidUser
     */
    public function testGetUserIdIfAllowedToLogin_invalidData(array $invalidUser): void
    {
        // In case validationException is not thrown
        $this->mock(UserFinderRepository::class); // Not relevant what findUserByEmail returns
        $this->mock(SecurityLoginChecker::class); // Return null on security checks

        /** @var LoginVerifier $loginVerifier */
        $loginVerifier = $this->container->get(LoginVerifier::class);

        $this->expectException(ValidationException::class);

        $loginVerifier->GetUserIdIfAllowedToLogin($invalidUser);
    }

    /**
     * Test getUserIdIfAllowedToLogin() with not existing user
     *
     * @dataProvider \App\Test\Provider\User\UserDataProvider::oneUserProvider()
     * @param array $validUser
     */
    public function testGetUserIdIfAllowedToLogin_userNotExisting(array $validUser): void
    {
        $this->mock(UserFinder::class)->method('findUserByEmail')->willReturn(new UserData());
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
     * @dataProvider \App\Test\Provider\User\UserDataProvider::oneUserObjectAndClientDataProvider()
     * @param array $userData values from client
     * @param UserData $userObj values from repository
     */
    public function testGetUserIdIfAllowedToLogin_invalidPass(array $userData, UserData $userObj): void
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
