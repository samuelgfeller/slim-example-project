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
     * @dataProvider \App\Test\Domain\User\UserProvider::userArrayReaderDataProvider
     * @param ArrayReader $userValues
     */
    public function testSettersAndGetters(ArrayReader $userValues): void
    {
        $givenId = $userValues->findInt('id');
        $givenName = $userValues->findString('name');
        $givenEmail = $userValues->getString('email');
        $givenPass1 = $userValues->findString('password');
        $givenPass2 = $userValues->findString('password2');
        $givenRole = $userValues->findString('role');

        // Depending if data is required in constructor it makes more or less sense to test setters
        $user = new User($userValues);

        $user->setId($givenId);
        $user->setName($givenName);
        $user->setEmail($givenEmail);
        $user->setPassword($givenPass1);
        $user->setPassword2($givenPass2);
        $user->setRole($givenRole);

        self::assertEquals($givenId, $user->getId());
        self::assertEquals($givenName, $user->getName());
        self::assertEquals($givenEmail, $user->getEmail());
        self::assertEquals($givenPass1, $user->getPassword());
        self::assertEquals($givenPass2, $user->getPassword2());
        self::assertEquals($givenRole, $user->getRole());
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