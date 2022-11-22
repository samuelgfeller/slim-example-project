<?php

namespace App\Domain\User\Data;

use App\Common\ArrayReader;
use App\Common\DateTimeImmutable;
use App\Domain\User\Enum\UserStatus;

/**
 * Class User also serving as DTO for simplicity reasons. More details on slim-api-example/issues/2
 * Public attributes: Basically if it is intended to interface DTOs or there may be read-only fields it makes
 * sense to keep them private otherwise not really.
 *
 */
class UserData
{
    // Variable names matching database columns (camelCase instead of snake_case)
    public ?int $id; // Mysql always returns string from db https://stackoverflow.com/a/5323169/9013718
    public ?string $firstName;
    public ?string $surname;
    // Email has to be default null as it is indicator that user obj is empty in AuthService register function
    public ?string $email;
    public ?string $password;
    public ?string $password2;
    public ?string $passwordHash;
    public ?UserStatus $status = null;
    public ?int $userRoleId = null;
    public ?DateTimeImmutable $updatedAt;
    public ?DateTimeImmutable $createdAt;
    // When adding a new attribute that should be editable with updateUser() it has to be added to authorization and service

    /**
     * User constructor.
     * @param array $userData
     * @param bool $notRestricted With or without security related attributes (has to be default false e.g. for hydrate())
     */
    public function __construct(array $userData = [], bool $notRestricted = false)
    {
        $arrayReader = new ArrayReader($userData);
        // Keys may be taken from client form or database so they have to correspond to both; otherwise use mapper
        // ArrayReader findDatatype casts the values in the wanted format too
        $this->id = $arrayReader->findAsInt('id');
        $this->firstName = $arrayReader->findAsString('first_name');
        $this->surname = $arrayReader->findAsString('surname');
        $this->email = $arrayReader->findAsString('email');
        $this->password = $arrayReader->findAsString('password');
        $this->password2 = $arrayReader->findAsString('password2');
        $this->passwordHash = $arrayReader->findAsString('password_hash');
        $this->updatedAt = $arrayReader->findAsDateTimeImmutable('updated_at');
        $this->createdAt = $arrayReader->findAsDateTimeImmutable('created_at');

        // Making sure that role and status aren't filled with malicious data
        if ($notRestricted === true){
            $this->status = $arrayReader->findAsEnum('status', UserStatus::class);
            $this->userRoleId = $arrayReader->findAsInt('user_role_id');
        }
    }

    /**
     * Returns the first and surname in one string separated by a whitespace
     * @return string
     */
    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->surname;
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
            'first_name' => $this->firstName,
            'surname' => $this->surname,
            'email' => $this->email,
            'password_hash' => $this->passwordHash,
            'user_role_id' => $this->userRoleId,
            'status' => $this->status->value,
        ];
    }

}
