<?php

namespace App\Test\Domain\Auth;

use App\Domain\Auth\AuthService;
use App\Domain\Exceptions\InvalidCredentialsException;
use App\Domain\Exceptions\ValidationException;
use App\Domain\User\User;
use App\Domain\User\UserService;
use App\Domain\Utility\ArrayReader;
use App\Infrastructure\Post\PostRepository;
use App\Test\UnitTestUtil;
use PHPUnit\Framework\TestCase;

class AuthServiceTest extends TestCase
{
    use UnitTestUtil;

    /**
     * Test GetUserIdIfAllowedToLogin
     *
     * @dataProvider \App\Test\Domain\User\UserProvider::oneUserProvider()
     * @param array $validUser
     */
    public function testGetUserIdIfAllowedToLogin(array $validUser)
    {
        // Service function uses password_verify which compares password with hash
        $userWithHashPass = $validUser;
        $userWithHashPass['password'] = password_hash($validUser['password'], PASSWORD_DEFAULT);

        $this->mock(UserService::class)->method('findUserByEmail')->willReturn($userWithHashPass);

        /** @var AuthService $authService */
        $authService = $this->container->get(AuthService::class);

        $userObj = new User(new ArrayReader($validUser));

        self::assertEquals($validUser['id'], $authService->GetUserIdIfAllowedToLogin($userObj));
    }

    /**
     * Test GetUserIdIfAllowedToLogin with invalid user data
     *
     * @dataProvider \App\Test\Domain\User\UserProvider::invalidEmailAndPasswordsUsersProvider()
     * @param array $validUser
     */
    public function testGetUserIdIfAllowedToLoginInvalidData(array $validUser)
    {
        // Technically not needed because if code works, it shouldn't go past the validation call line
        // But in case test fails (exception not thrown) error would not be accurate without this mock
        // Service function uses password_verify which compares password with hash
        $userWithHashPass = $validUser;
        $userWithHashPass['password'] = password_hash($validUser['password'], PASSWORD_DEFAULT);

        $this->mock(UserService::class)->method('findUserByEmail')->willReturn($userWithHashPass);

        /** @var AuthService $authService */
        $authService = $this->container->get(AuthService::class);

        $userObj = new User(new ArrayReader($validUser));

        $this->expectException(ValidationException::class);

        $authService->GetUserIdIfAllowedToLogin($userObj);
    }

    /**
     * Test GetUserIdIfAllowedToLogin with not existing user
     *
     * @dataProvider \App\Test\Domain\User\UserProvider::oneUserProvider()
     * @param array $validUser
     */
    public function testGetUserIdIfAllowedToLoginUserNotExisting(array $validUser)
    {
        $this->mock(UserService::class)->method('findUserByEmail')->willReturn(null);

        /** @var AuthService $authService */
        $authService = $this->container->get(AuthService::class);

        $userObj = new User(new ArrayReader($validUser));

        $this->expectException(InvalidCredentialsException::class);

        $authService->GetUserIdIfAllowedToLogin($userObj);
    }

    /**
     * Test GetUserIdIfAllowedToLogin with invalid password
     * important to test this method extensively for security
     *
     * @dataProvider \App\Test\Domain\User\UserProvider::oneUserProvider()
     * @param array $validUser
     */
    public function testGetUserIdIfAllowedToLoginInvalidCreds(array $validUser)
    {
        // Add DIFFERENT password hash
        $userWithHashPass = $validUser;
        $userWithHashPass['password'] = password_hash($validUser['password'] . 'differentPass', PASSWORD_DEFAULT);
        $this->mock(UserService::class)->method('findUserByEmail')->willReturn($userWithHashPass);

        /** @var AuthService $authService */
        $authService = $this->container->get(AuthService::class);

        $userObj = new User(new ArrayReader($validUser));

        $this->expectException(InvalidCredentialsException::class);

        $authService->GetUserIdIfAllowedToLogin($userObj);
    }

    /**
     *
     *
     * @dataProvider \App\Test\Domain\User\UserProvider::oneUserProvider()
     * @param array $validUser
     */
//    public function testGenerateToken(array $validUser)
//    {
//        $user = new User(new ArrayReader($validUser));
//
//    }
//
//    public function testGetUserRole()
//    {
//    }


}
