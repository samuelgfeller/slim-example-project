<?php

namespace App\Test\Unit\Domain\User;

use App\Domain\Exceptions\ValidationException;
use App\Domain\User\Service\UserUpdater;
use App\Infrastructure\User\UserRepository;
use App\Test\AppTestTrait;
use PHPUnit\Framework\TestCase;

class UserUpdaterTest extends TestCase
{
    use AppTestTrait;

    /**
     * Test updateUser() from UserUpdater
     *
     * @dataProvider \App\Test\Provider\UserProvider::oneUserProvider()
     * @param array $validUser
     */
    public function testUpdateUser(array $validUser): void
    {
        $userRepositoryMock = $this->mock(UserRepository::class);
        $userRepositoryMock->expects(self::once())->method('updateUser')->willReturn(true);
        // Used in Validation to check user existence
        $userRepositoryMock->method('userExists')->willReturn(true);

        $service = $this->container->get(UserUpdater::class);

        self::assertTrue($service->updateUser($validUser['id'], $validUser, 1));
    }

    /**
     * Test updateUser() with invalid users
     * Test that data from existing user is validated before being updated
     *
     * @dataProvider \App\Test\Provider\UserProvider::invalidUserForUpdate()
     * @param array $invalidUser
     */
    public function testUpdateUser_invalid(array $invalidUser): void
    {
        // Mock UserRepository because it is used by the validation logic
        // In this test user exists so every invalid data from invalidUserProvider() can throw
        // its error. Otherwise there would be always the error because of the exist and each data
        // could not be tested
        $this->mock(UserRepository::class)->method('userExists')->willReturn(true);

        $service = $this->container->get(UserUpdater::class);

        $this->expectException(ValidationException::class);

        $service->updateUser($invalidUser['id'], $invalidUser, 1);
    }

    /**
     * Test updateUser() when user doesn't exist
     *
     * @dataProvider \App\Test\Provider\UserProvider::oneUserProvider()
     * @param array $validUser
     */
    public function testUpdateUser_notExisting(array $validUser): void
    {
        // Mock UserRepository because it is used by the validation logic
        // Point of this test is not existing user
        $this->mock(UserRepository::class)->method('userExists')->willReturn(false);

        $service = $this->container->get(UserUpdater::class);

        $this->expectException(ValidationException::class);

        $service->updateUser($validUser['id'], $validUser, 1);
    }
}


