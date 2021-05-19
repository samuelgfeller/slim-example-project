<?php

namespace App\Domain\User\DTO;

use App\Domain\Utility\ArrayReader;

/**
 * Class User also serving as DTO for simplicity reasons. More details on slim-api-example/issues/2
 * Public attributes: Basically if it is intended to interface DTOs or there may be read-only fields it makes
 * sense to keep them private otherwise not really.
 *
 */
class User
{
    public ?int $id; // Mysql always returns string from db https://stackoverflow.com/a/5323169/9013718
    public ?string $name;
    // Email has to be default null as it is indicator that user obj is empty in AuthService register function
    public ?string $email;
    public ?string $password;
    public ?string $password2;
    public ?string $passwordHash;
    public ?string $status = null;
    public ?string $role = null;
    // When adding a new attribute that should be editable with updateUser() it has to be added there

    public const STATUS_UNVERIFIED = 'unverified'; // Default after registration
    public const STATUS_ACTIVE = 'active'; // Verified via token received in email
    public const STATUS_LOCKED = 'locked'; // Locked for security reasons, may be reactivated by account holder via email
    public const STATUS_SUSPENDED = 'suspended'; // User suspended, account holder not allowed to login even via email

    /**
     * User constructor.
     * @param array $userData
     * @param bool $limited With or without security related attributes (has to be default false e.g. for hydrate())
     */
    public function __construct(array $userData = [], bool $limited = false)
    {
        $arrayReader = new ArrayReader($userData);
        // Keys may be taken from client form or database so they have to correspond to both; otherwise use mapper
        // ArrayReader findDatatype casts the values in the wanted format too
        $this->id = $arrayReader->findInt('id');
        $this->name = $arrayReader->findString('name');
        $this->email = $arrayReader->findString('email');
        $this->password = $arrayReader->findString('password');
        $this->password2 = $arrayReader->findString('password2');
        $this->passwordHash = $arrayReader->findString('password_hash');

        // Making sure that role and status aren't filled with malicious data
        if ($limited === false){
            $this->status = $arrayReader->findString('status');
            $this->role = $arrayReader->findString('role');
        }
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

}
