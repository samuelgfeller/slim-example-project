<?php

namespace App\Test\Domain\User;

use App\Domain\User\UserService;
use App\Domain\User\UserValidation;
use App\Infrastructure\User\UserRepository;
use Cake\Datasource\RepositoryInterface;
use PHPUnit\Framework\TestCase;
use Slim\Logger;

class UserServiceTest extends TestCase
{
    private array $users;

    public function setUp()
    {
        $userProvider = new UserProvider();
        $this->users = $userProvider->getSampleUsers();
    }

    public function testFindAllUsers()
    {
        $repoStub = $this->createMock(UserRepository::class);
        $repoStub->method('findAllUsers')->willReturn($this->users);

        // Here I want to instantiate UserService with my custom repo stub but then I have to add the other dependencies as well
        // They are only empty instances and they don't matter in this test case so it works but is that correct? What should / could I do else?
        $service = new UserService($repoStub, new UserValidation(new Logger(), $repoStub), new Logger());

        $this->assertEquals($this->users, $service->findAllUsers());
    //      Test passes
    }

    public function testFindUser()
    {
        $user = $this->users[0];

        $repoStub = $this->createMock(UserRepository::class);
        $repoStub->method('findUserById')->willReturn($user);

        $service = new UserService($repoStub, new UserValidation(new Logger(), $repoStub), new Logger());

        $this->assertEquals($user, $service->findUser($user['id']));
    }

    public function testFindUserByEmail()
    {
        $user = $this->users[0];

        $repoStub = $this->createMock(UserRepository::class);
        $repoStub->method('findUserByEmail')->willReturn($user);

        $service = new UserService($repoStub, new UserValidation(new Logger(), $repoStub), new Logger());

        $this->assertEquals($user, $service->findUserByEmail($user['email']));
    }

    public function testCreateUser()
    {
        $validUser = $this->users[0];

        $repoStub = $this->createMock(UserRepository::class);
        $repoStub->method('insertUser')->willReturn($validUser['id']);

        $service = new UserService($repoStub, new UserValidation(new Logger(), $repoStub), new Logger());

        $this->assertEquals($user, $service->findUserByEmail($user['email']));
    }

    public function testUpdateUser()
    {
    }

    public function testDeleteUser()
    {
    }


}
