<?php


namespace App\Test\Unit\User;


use App\Domain\User\Data\UserData;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{

    protected UserData $user;

    /**
     * Testing all getters of the class user
     *
     * I dont know if this method is actually useful since
     * it doesn't improve coverage (methods are used in other tests)
     * and there isn't any logic in these methods. This is an example
     * but I won't do it for other objects.
     *
     * @dataProvider \App\Test\Provider\User\UserDataProvider::validUserProvider()
     * @param array $userValues
     */
    public function testGetters(array $userValues): void
    {
        $user = new UserData($userValues);

        // Set possible values via setters
        $user->password = $userValues['password'];
        $user->passwordHash = $userValues['password_hash'];

        self::assertEquals($userValues['id'], $user->id);
        self::assertEquals($userValues['name'], $user->name);
        self::assertEquals($userValues['email'], $user->email);
        self::assertEquals($userValues['password'], $user->password);
        self::assertEquals($userValues['password2'], $user->password2);
    }

    /**
     * Testing the function toArrayForDatabase()
     *
     * @dataProvider \App\Test\Provider\User\UserDataProvider::validUserProvider()
     * @param array $userValues
     */
    public function testToArrayForDatabase(array $userValues): void
    {
        // Instantiating the User object
        $user = new UserData($userValues);

        // Call said function to get the values as array
        $userAsDbArray = $user->toArrayForDatabase();

        // Check if values match
        self::assertEquals($userValues['id'], $userAsDbArray['id']);
        self::assertEquals($userValues['name'], $userAsDbArray['name']);
        self::assertEquals($userValues['email'], $userAsDbArray['email']);
        self::assertEquals($userValues['password_hash'], $userAsDbArray['password_hash']);
        // role defaults to user and cannot be set via constructor
        self::assertEquals($user->role, $userAsDbArray['role']);
    }
}