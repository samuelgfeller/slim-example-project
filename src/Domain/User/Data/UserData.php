<?php

namespace App\Domain\User\Data;

use App\Domain\User\Enum\UserStatus;

/**
 * Class User also serving as DTO for simplicity reasons. More details on slim-api-example/issues/2
 * Public attributes: Basically if it is intended to interface DTOs or there may be read-only fields it makes
 * sense to keep them private otherwise not really.
 */
class UserData implements \JsonSerializable
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
    public ?\DateTimeImmutable $updatedAt;
    public ?\DateTimeImmutable $createdAt;
    // When adding a new attribute that should be editable with updateUser() it has to be added to authorization and service

    /**
     * User constructor.
     *
     * @param array $userData
     *
     * @throws \Exception
     */
    public function __construct(array $userData = [])
    {
        // Keys may be taken from view form or database, so they have to correspond to both; otherwise use mapper
        $this->id = $userData['id'] ?? null;
        $this->firstName = $userData['first_name'] ?? null;
        $this->surname = $userData['surname'] ?? null;
        $this->email = $userData['email'] ?? null;
        $this->password = $userData['password'] ?? null;
        $this->password2 = $userData['password2'] ?? null;
        $this->passwordHash = $userData['password_hash'] ?? null;
        $this->updatedAt = $userData['updated_at'] ?? null ? new \DateTimeImmutable($userData['updated_at']) : null;
        $this->createdAt = $userData['created_at'] ?? null ? new \DateTimeImmutable($userData['created_at']) : null;
        $this->status = $userData['status'] ?? null ? UserStatus::tryFrom($userData['status']) : null;
        // Empty check is for testUserSubmitCreate_invalid test function where user_role_id is an empty string
        $this->userRoleId = !empty($userData['user_role_id'] ?? null) ? $userData['user_role_id'] : null;
    }

    /**
     * Returns the first and surname in one string separated by a whitespace.
     *
     * @return string
     */
    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->surname;
    }

    /**
     * Returns values of object as array for database (pw2 not included).
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

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'firstName' => $this->firstName,
            'surname' => $this->surname,
            'email' => $this->email,
            'status' => $this->status,
            'userRoleId' => $this->userRoleId,
            'updatedAt' => $this->updatedAt->format('Y-m-d H:i:s'),
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
        ];
    }
}
