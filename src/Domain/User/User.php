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
    private ?string $passwordHash;
    private ?string $role;
    
    
    public function __construct(ArrayReader $arrayReader)
    {
        // These keys have to match the input key for the ArrayReader
        $this->id = $arrayReader->findInt('id');
        $this->name = $arrayReader->findString('name');
        $this->email = $arrayReader->getString('email');
        $this->password = $arrayReader->findString('password');
        $this->password2 = $arrayReader->findString('password2');
        $this->passwordHash = $arrayReader->findString('password_hash');
        $this->role = $arrayReader->findString('role') ?? 'user';
    }

    /**
     * Returns values of object as array for database (pw2 not included)
     *
     * The array keys MUST match with the database column names since it can
     * be used to modify a database entry
     *
     * @return array
     */
    public function toArrayForDatabase(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'password_hash' => $this->passwordHash,
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
     * @return mixed|string|null
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * @return mixed|string|null
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return mixed|string|null
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string|null $password
     */
    public function setPassword(?string $password): void
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
     * @param string|null $passwordHash
     */
    public function setPasswordHash(?string $passwordHash): void
    {
        $this->passwordHash = $passwordHash;
    }
}
