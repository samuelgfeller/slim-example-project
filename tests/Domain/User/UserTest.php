<?php


namespace Domain\User;


use App\Domain\User\User;
use App\Domain\Utility\ArrayReader;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{

    protected User $user;

    public function userDataProvider(): array
    {
        return [
            [new ArrayReader(['id' => 1, 'name' => 'Bill Gates', 'email' => 'gates@email.com', 'password' => '12345678', 'password2' => '12345678', 'role' => 'admin'])],
            [new ArrayReader(['id' => 2, 'name' => 'Steve Jobs', 'email' => 'jobs@email.com', 'password' => '12345678', 'password2' => '12345678', 'role' => 'user'])],
            [new ArrayReader(['id' => 3, 'name' => 'Mark Zuckerberg', 'email' => 'zuckerberg@email.com', 'password' => '12345678', 'password2' => '12345678', 'role' => 'user'])],
            [new ArrayReader(['id' => 4, 'name' => 'Evan Spiegel', 'email' => 'spiegel@email.com', 'password' => '12345678', 'password2' => '12345678', 'role' => 'user'])],
            [new ArrayReader(['id' => 5, 'name' => 'Jack Dorsey', 'email' => 'dorsey@email.com', 'password' => '12345678', 'password2' => '12345678', 'role' => 'user'])],
        ];
    }

    /**
     * Testing all getters of the class user
     *
     * @dataProvider userDataProvider
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

    /**
     * Testing the function toArrayForDatabase()
     *
     * @dataProvider userDataProvider
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