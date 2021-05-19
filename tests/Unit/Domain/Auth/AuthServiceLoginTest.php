<?php

namespace App\Test\Unit\Domain\Auth;

use App\Domain\Auth\AuthService;
use App\Domain\Exceptions\InvalidCredentialsException;
use App\Domain\Exceptions\ValidationException;
use App\Domain\Security\SecurityService;
use App\Domain\User\DTO\User;
use App\Domain\User\UserService;
use App\Infrastructure\User\UserRepository;
use App\Test\AppTestTrait;
use PHPUnit\Framework\TestCase;

class AuthServiceLoginTest extends TestCase
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
        $this->mock(SecurityService::class); // Return null on security checks

        /** @var AuthService $authService */
        $authService = $this->container->get(AuthService::class);

        self::assertEquals($repoUser->id, $authService->GetUserIdIfAllowedToLogin($validUserData));
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
        $this->mock(SecurityService::class); // Return null on security checks

        /** @var AuthService $authService */
        $authService = $this->container->get(AuthService::class);

        $this->expectException(ValidationException::class);

        $authService->GetUserIdIfAllowedToLogin($invalidUser);
    }

    /**
     * Test getUserIdIfAllowedToLogin() with not existing user
     *
     * @dataProvider \App\Test\Provider\UserProvider::oneUserProvider()
     * @param array $validUser
     */
    public function testGetUserIdIfAllowedToLogin_userNotExisting(array $validUser): void
    {
        $this->mock(UserService::class)->method('findUserByEmail')->willReturn(new User());
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
     * @dataProvider \App\Test\Provider\UserProvider::oneUserObjectAndClientDataProvider()
     * @param array $userData values from client
     * @param User $userObj values from repository
     */
    public function testGetUserIdIfAllowedToLogin_invalidPass(array $userData, User $userObj): void
    {
        // Add DIFFERENT password hash
        $userData['password_hash'] = password_hash('differentPass', PASSWORD_DEFAULT);

        // findUserByEmail() used in $authService->GetUserIdIfAllowedToLogin()
        $this->mock(UserService::class)->method('findUserByEmail')->willReturn($userObj);
        $this->mock(SecurityService::class); // Return null on security checks

        /** @var AuthService $authService */
        $authService = $this->container->get(AuthService::class);

        $this->expectException(InvalidCredentialsException::class);

        $authService->GetUserIdIfAllowedToLogin($userData);
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
        $this->mock(UserRepository::class)->method('getUserRoleById')->willReturn($user['role']);

        /** @var AuthService $authService */
        $authService = $this->container->get(AuthService::class);

        self::assertEquals($user['role'], $authService->getUserRoleById($user['id']));
    }
}
