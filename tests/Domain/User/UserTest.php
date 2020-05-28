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
        $user = new User($userValues);

        $this->assertEquals($userValues->findInt('id'), $user->getId());
        $this->assertEquals($userValues->findString('name'), $user->getName());
        $this->assertEquals($userValues->getString('email'), $user->getEmail());
        $this->assertEquals($userValues->findString('password'), $user->getPassword());
        $this->assertEquals($userValues->findString('password2'), $user->getPassword2());
        $this->assertEquals($userValues->findString('role'), $user->getRole());
    }

    /**y§
     * Testing the function toArrayForDatabase()
     *
     * @dataProvider \App\Test\Domain\User\UserProvider::userArrayReaderDataProvider
     * @param ArrayReader $userValues
     */
    public function testToArrayForDatabaseFunction(ArrayReader $userValues): void
    {
        // Instantiating the User object
        $user = new User($userValues);

        // Call said function to get the values as array
        $userAsArray = $user->toArrayForDatabase();

        // Check if values match
        $this->assertEquals($userValues->findInt('id'), $userAsArray['id']);
        $this->assertEquals($userValues->findString('name'), $userAsArray['name']);
        $this->assertEquals($userValues->getString('email'), $userAsArray['email']);
        $this->assertEquals($userValues->findString('password'), $userAsArray['password']);
        $this->assertEquals($userValues->findString('role'), $userAsArray['role']);
    }
}