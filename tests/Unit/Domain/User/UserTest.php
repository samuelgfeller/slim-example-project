<?php


namespace App\Test\Unit\Domain\User;


use App\Domain\User\User;
use App\Domain\Utility\ArrayReader;
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
     * @dataProvider \App\Test\Provider\UserProvider::userArrayReaderDataProvider
     * @param ArrayReader $userValues
     */
    public function testGetters(ArrayReader $userValues): void
    {
        $user = new User($userValues);

        // Set possible values via setters
        $user->setPassword($userValues->findString('password'));
        $user->setPasswordHash($userValues->findString('password_hash'));

        self::assertEquals($userValues->findInt('id'), $user->getId());
        self::assertEquals($userValues->findString('name'), $user->getName());
        self::assertEquals($userValues->getString('email'), $user->getEmail());
        self::assertEquals($userValues->findString('password'), $user->getPassword());
        self::assertEquals($userValues->findString('password2'), $user->getPassword2());
    }

    /**
     * Testing the function toArrayForDatabase()
     *
     * @dataProvider \App\Test\Provider\UserProvider::userArrayReaderDataProvider
     * @param ArrayReader $userValues
     */
    public function testToArrayForDatabase(ArrayReader $userValues): void
    {
        // Instantiating the User object
        $user = new User($userValues);

        // Call said function to get the values as array
        $userAsDbArray = $user->toArrayForDatabase();

        // Check if values match
        self::assertEquals($userValues->findInt('id'), $userAsDbArray['id']);
        self::assertEquals($userValues->findString('name'), $userAsDbArray['name']);
        self::assertEquals($userValues->getString('email'), $userAsDbArray['email']);
        self::assertEquals($userValues->findString('password_hash'), $userAsDbArray['password_hash']);
        self::assertEquals($userValues->findString('role'), $userAsDbArray['role']);
    }
}