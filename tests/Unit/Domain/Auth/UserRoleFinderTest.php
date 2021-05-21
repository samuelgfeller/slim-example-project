<?php


namespace App\Test\Unit\Domain\Auth;

use App\Domain\Authentication\Service\UserRoleFinder;
use App\Infrastructure\User\UserRepository;
use App\Test\AppTestTrait;
use PHPUnit\Framework\TestCase;

class UserRoleFinderTest extends TestCase
{
    use AppTestTrait;

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

        $userRoleFinder = $this->container->get(UserRoleFinder::class);

        self::assertEquals($user['role'], $userRoleFinder->getUserRoleById($user['id']));
    }
}