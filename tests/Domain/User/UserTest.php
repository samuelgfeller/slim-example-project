<?php


namespace App\Test\Domain\User;


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
     * @dataProvider \App\Test\Domain\User\UserProvider::userArrayReaderDataProvider
     * @param ArrayReader $userValues
     */
    public function testGetters(ArrayReader $userValues): void
    {
        $user = new User($userValues);

        // Set possible values via setters
        $user->setId($userValues->findInt('id'));
        $user->setPassword($userValues->findString('password'));
        $user->setRole($userValues->findString('role'));

        self::assertEquals($userValues->findInt('id'), $user->getId());
        self::assertEquals($userValues->findString('name'), $user->getName());
        self::assertEquals($userValues->getString('email'), $user->getEmail());
        self::assertEquals($userValues->findString('password'), $user->getPassword());
        self::assertEquals($userValues->findString('password2'), $user->getPassword2());
        self::assertEquals($userValues->findString('role'), $user->getRole());
    }

    /**
     * Testing the function toArrayForDatabase()
     *
     * @dataProvider \App\Test\Domain\User\UserProvider::userArrayReaderDataProvider
     * @param ArrayReader $userValues
     */
    public function testToArrayForDatabase(ArrayReader $userValues): void
    {
        // Instantiating the User object
        $user = new User($userValues);

        // Call said function to get the values as array
        $userAsArray = $user->toArrayForDatabase();

        // Check if values match
        self::assertEquals($userValues->findInt('id'), $userAsArray['id']);
        self::assertEquals($userValues->findString('name'), $userAsArray['name']);
        self::assertEquals($userValues->getString('email'), $userAsArray['email']);
        self::assertEquals($userValues->findString('password'), $userAsArray['password']);
        self::assertEquals($userValues->findString('role'), $userAsArray['role']);
    }
}