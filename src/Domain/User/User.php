<?php

namespace App\Domain\User;

use App\Domain\Utility\ArrayReader;

class User
{
    private int $id;
    private string $name;
    private string $email;
    private string $password;
    private string $role;


    public function __construct(ArrayReader $arrayReader) {
        $this->id = $arrayReader->findInt('id');
        $this->name = $arrayReader->getString('name');
        $this->email = $arrayReader->getString('email');
        $this->password = $arrayReader->getString('password');
        $this->role = $arrayReader->findString('role');
    }

    /**
     * @return int|mixed|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int|mixed|null $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed|string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed|string|null $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed|string|null
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed|string|null $email
     */
    public function setEmail($email): void
    {
        $this->email = $email;
    }

    /**
     * @return mixed|string|null
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed|string|null $password
     */
    public function setPassword($password): void
    {
        $this->password = $password;
    }

    /**
     * @return mixed|string|null
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param mixed|string|null $role
     */
    public function setRole($role): void
    {
        $this->role = $role;
    }


}
