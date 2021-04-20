<?php


namespace App\Test\Unit\Domain\User;


use App\Domain\User\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{

    protected User $user;

    /**
     * Testing all getters of the class user
     *
     * I dont know if this method is actually useful since
     * it doesn't improve coverage (methods are used in other tests)
     * and there isn't any logic in these methods. This is an example
     * but I won't do it for other objects.
     *
     * @dataProvider \App\Test\Provider\UserProvider::validUserProvider()
     * @param array $userValues
     */
    public function testGetters(array $userValues): void
    {
        $user = new User($userValues);

        // Set possible values via setters
        $user->setPassword($userValues['password']);
        $user->setPasswordHash($userValues['password_hash']);

        self::assertEquals($userValues['id'], $user->getId());
        self::assertEquals($userValues['name'], $user->getName());
        self::assertEquals($userValues['email'], $user->getEmail());
        self::assertEquals($userValues['password'], $user->getPassword());
        self::assertEquals($userValues['password2'], $user->getPassword2());
    }

    /**
     * Testing the function toArrayForDatabase()
     *
     * @dataProvider \App\Test\Provider\UserProvider::validUserProvider()
     * @param array $userValues
     */
    public function testToArrayForDatabase(array $userValues): void
    {
        // Instantiating the User object
        $user = new User($userValues);

        // Call said function to get the values as array
        $userAsDbArray = $user->toArrayForDatabase();

        // Check if values match
        self::assertEquals($userValues['id'], $userAsDbArray['id']);
        self::assertEquals($userValues['name'], $userAsDbArray['name']);
        self::assertEquals($userValues['email'], $userAsDbArray['email']);
        self::assertEquals($userValues['password_hash'], $userAsDbArray['password_hash']);
        // role defaults to user and cannot be set via constructor
        self::assertEquals('user', $userAsDbArray['role']);
    }
}