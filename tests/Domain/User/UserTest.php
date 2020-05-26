<?php


namespace App\Test\Domain\User;


use App\Domain\User\User;
use App\Domain\Utility\ArrayReader;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{

    protected User $user;

    public function getSampleUsers(): array
    {
        return [
           ['id' => 1, 'name' => 'Bill Gates', 'email' => 'gates@email.com', 'password' => '12345678', 'password2' => '12345678', 'role' => 'admin'],
           ['id' => 2, 'name' => 'Steve Jobs', 'email' => 'jobs@email.com', 'password' => '12345678', 'password2' => '12345678', 'role' => 'user'],
           ['id' => 3, 'name' => 'Mark Zuckerberg', 'email' => 'zuckerberg@email.com', 'password' => '12345678', 'password2' => '12345678', 'role' => 'user'],
           ['id' => 4, 'name' => 'Evan Spiegel', 'email' => 'spiegel@email.com', 'password' => '12345678', 'password2' => '12345678', 'role' => 'user'],
           ['id' => 5, 'name' => 'Jack Dorsey', 'email' => 'dorsey@email.com', 'password' => '12345678', 'password2' => '12345678', 'role' => 'user'],
        ];
    }

    /**
     *
     * @return array
     */
    public function userArrayReaderDataProvider(): array
    {
        $userValues = $this->getSampleUsers();

        // Amount of times each test will be executed with different data
        $maxAmountOfValuesToProvide = 5;
        $i = 0;
        $returnObjects = [];
        foreach ($userValues as $userValue){
            // User value has to be in additional array for dataProvider for the case where multiple arguments have to be passed through
            $returnObjects[] = new ArrayReader($userValue);

            $i++;
            if ($maxAmountOfValuesToProvide >= $i){
                break;
            }
        }
        return [$returnObjects];
    }


    /**
     * Testing all getters of the class user
     *
     * @dataProvider userArrayReaderDataProvider
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
     * @dataProvider userArrayReaderDataProvider
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