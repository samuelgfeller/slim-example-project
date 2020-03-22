<?php

namespace App\Domain\User;

use App\Domain\Utility\ArrayReader;

class User
{
    private ?int $id;
    private ?string $name;
    private string $email;
    private ?string $password;
    private ?string $password2;
    private ?string $role;
    
    
    public function __construct(ArrayReader $arrayReader)
    {
        // These keys have to math the input key for the ArrayReader
        $this->id = $arrayReader->findInt('id');
        $this->name = $arrayReader->findString('name');
        $this->email = $arrayReader->getString('email');
        $this->password = $arrayReader->findString('password');
        $this->password2 = $arrayReader->findString('password2');
        $this->role = $arrayReader->findString('role') ?? 'user';
    }
    
    
    /**
     * Returns all values of object as array.
     * The array keys should match with the database
     * column names since it is likely used to
     * modify a database table
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'role' => $this->role,
        ];
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
     * @return string|null
     */
    public function getPassword2(): ?string
    {
        return $this->password2;
    }

    /**
     * @param string|null $password2
     */
    public function setPassword2(?string $password2): void
    {
        $this->password2 = $password2;
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
