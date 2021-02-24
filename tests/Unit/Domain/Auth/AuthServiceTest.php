<?php

namespace App\Test\Unit\Domain\Auth;

use App\Domain\Auth\AuthService;
use App\Domain\Exceptions\InvalidCredentialsException;
use App\Domain\Exceptions\ValidationException;
use App\Domain\User\User;
use App\Domain\User\UserService;
use App\Domain\Utility\ArrayReader;
use App\Infrastructure\Post\PostRepository;
use App\Infrastructure\User\UserRepository;
use App\Test\AppTestTrait;
use PHPUnit\Framework\TestCase;

class AuthServiceTest extends TestCase
{
    use AppTestTrait;

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

        /** @var AuthService $authService */
        $authService = $this->container->get(AuthService::class);

        $userObj = new User(new ArrayReader($validUser));

        self::assertEquals($validUser['id'], $authService->GetUserIdIfAllowedToLogin($userObj));
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

        /** @var AuthService $authService */
        $authService = $this->container->get(AuthService::class);

        $userObj = new User(new ArrayReader($validUser));

        $this->expectException(ValidationException::class);

        $authService->GetUserIdIfAllowedToLogin($userObj);
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

        /** @var AuthService $authService */
        $authService = $this->container->get(AuthService::class);

        $userObj = new User(new ArrayReader($validUser));

        $this->expectException(InvalidCredentialsException::class);

        $authService->GetUserIdIfAllowedToLogin($userObj);
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

        /** @var AuthService $authService */
        $authService = $this->container->get(AuthService::class);

        $userObj = new User(new ArrayReader($validUser));

        $this->expectException(InvalidCredentialsException::class);

        $authService->GetUserIdIfAllowedToLogin($userObj);
    }

    /**
     * Test getUserRole() with different roles
     *
     * Test with multiple users to have different roles
     * @dataProvider \App\Test\Provider\UserProvider::validUserProvider()
     * @param array $user
     */
    public function testGetUserRole(array $user): void
    {
        $this->mock(UserRepository::class)->method('getUserRole')->willReturn($user['role']);

        /** @var AuthService $authService */
        $authService = $this->container->get(AuthService::class);

        self::assertEquals($user['role'], $authService->getUserRole($user['id']));
    }
}