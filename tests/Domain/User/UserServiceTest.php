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
    public function testFindAllUsers()
    {
        $userProvider = new UserProvider();
        $users = $userProvider->getSampleUsers();

        $repoStub = $this->createMock(UserRepository::class);
        $repoStub->method('findAllUsers')->willReturn($users);

        // Here I want to instantiate UserService with my custom repo stub but then I have to add the other dependencies as well
        // They are only empty instances and they don't matter in this test case so it could work but is that correct? What should / could I do else?
        $service = new UserService($repoStub, new UserValidation(new Logger(), $repoStub), new Logger());

        $this->assertEquals($users, $service->findAllUsers());
//      Test passes
    }

    public function testFindUser()
    {
    }

    public function testFindUserByEmail()
    {
    }

    public function testCreateUser()
    {
    }

    public function testUpdateUser()
    {
    }

    public function testDeleteUser()
    {
    }


}
