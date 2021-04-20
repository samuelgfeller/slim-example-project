<?php

namespace App\Domain\User;

use App\Domain\Utility\ArrayReader;

class User
{

    private ?string $id; // Mysql always returns string from db https://stackoverflow.com/a/5323169/9013718
    private ?string $name;
    private ?string $email;
    private ?string $password;
    private ?string $password2;
    private ?string $passwordHash;
    private ?string $status;
    private ?string $role;

    public const STATUS_UNVERIFIED = 'unverified'; // Default after registration
    public const STATUS_ACTIVE = 'active'; // Verified via token received in email
    public const STATUS_LOCKED = 'locked'; // Locked for security reasons, may be reactivated by account holder via email
    public const STATUS_SUSPENDED = 'suspended'; // User suspended, account holder not allowed to login even via email
    
    public function __construct(array $userData = [])
    {
        $arrayReader = new ArrayReader($userData);
        // Values directly taken from client form. It should be made sure that non-allowed keys are not set but
        // better be safe than sorry. Sensitive values like role and status can be changed later.
        $this->id = $arrayReader->findString('id');
        $this->name = $arrayReader->findString('name');
        $this->email = $arrayReader->getString('email');
        $this->password = $arrayReader->findString('password');
        $this->password2 = $arrayReader->findString('password2');
        $this->passwordHash = $arrayReader->findString('password_hash');
        // Making sure that role is always user to prevent that someone tries to have admin access by adding
        // role in request body; default values are set
        $this->role = 'user';
        $this->status = self::STATUS_UNVERIFIED;
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
            'status' => $this->status,
        ];
    }

    /**
     * @param string|null $id
     */
    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
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

    /**
     * @param string|null $status
     */
    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }


}
