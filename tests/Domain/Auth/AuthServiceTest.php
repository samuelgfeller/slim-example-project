<?php

namespace App\Test\Domain\Auth;

use App\Domain\Auth\AuthService;
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
     * @dataProvider \App\Test\Domain\User\UserProvider::oneUserProvider()
     * @param array $validUser
     */
    public function testGetUserWithIdIfAllowedToLogin(array $validUser)
    {
        // Service function uses password_verify which compares password with hash
        $userWithHashPass = $validUser;
        $userWithHashPass['password'] = password_hash($validUser['password'], PASSWORD_DEFAULT);

        $this->mock(UserService::class)->method('findUserByEmail')->willReturn($userWithHashPass);

        /** @var AuthService $authService */
        $authService = $this->container->get(AuthService::class);

        $userObj = new User(new ArrayReader($validUser));

        self::assertEquals($userObj, $authService->getUserWithIdIfAllowedToLogin($userObj));
    }

    public function testGenerateToken()
    {
    }

    public function testGetUserRole()
    {
    }


}
