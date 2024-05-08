<?php

namespace App\Domain\User\Data;

use App\Domain\User\Enum\UserLang;
use App\Domain\User\Enum\UserStatus;
use App\Domain\User\Enum\UserTheme;

class UserData implements \JsonSerializable
{
    // Variable names matching database columns (camelCase instead of snake_case)
    public ?int $id; // Mysql always returns string from db https://stackoverflow.com/a/5323169/9013718
    public ?string $firstName;
    public ?string $lastName;
    // Email has to be default null as it is an indicator that user obj is empty in AuthService register function
    public ?string $email;
    public ?string $password;
    public ?string $password2;
    public ?string $passwordHash;
    public ?UserStatus $status = null;
    public ?UserTheme $theme = null;
    public UserLang $language = UserLang::English;
    public ?int $userRoleId = null;
    public ?\DateTimeImmutable $updatedAt;
    public ?\DateTimeImmutable $createdAt;
    // When adding a new attribute that should be editable with updateUser() it has to be added to authorization and service

    public function __construct(array $userData = [])
    {
        // Keys may be taken from view form or database, so they have to correspond to both; otherwise use mapper
        $this->id = $userData['id'] ?? null;
        $this->firstName = $userData['first_name'] ?? null;
        $this->lastName = $userData['last_name'] ?? null;
        $this->email = $userData['email'] ?? null;
        $this->password = $userData['password'] ?? null;
        $this->password2 = $userData['password2'] ?? null;
        $this->passwordHash = $userData['password_hash'] ?? null;
        $this->theme = $userData['theme'] ?? null ? UserTheme::tryFrom($userData['theme']) : null;
        $this->language = UserLang::tryFrom($userData['language'] ?? '') ?? UserLang::English;
        // It may be useful to surround the datetime values with try catch in the user data constructor as object
        // can be created before validation
        $this->updatedAt = $userData['updated_at'] ?? null ? new \DateTimeImmutable($userData['updated_at']) : null;
        $this->createdAt = $userData['created_at'] ?? null ? new \DateTimeImmutable($userData['created_at']) : null;
        $this->status = isset($userData['status']) ? UserStatus::tryFrom($userData['status']) : null;
        // Empty check is for testUserSubmitCreate_invalid test function where user_role_id is an empty string
        $this->userRoleId = !empty($userData['user_role_id']) ? $userData['user_role_id'] : null;
    }

    /**
     * Returns the first and lastName in one string separated by a whitespace.
     *
     * @return string
     */
    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    /**
     * Returns values of object as array for database (pw2 not included).
     *
     * The array keys MUST match with the database column names.
     *
     * @return array
     */
    public function toArrayForDatabase(): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'password_hash' => $this->passwordHash,
            'user_role_id' => $this->userRoleId,
            'status' => $this->status?->value,
            'theme' => $this->theme?->value,
            'language' => $this->language->value,
        ];
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'email' => $this->email,
            'status' => $this->status,
            'userRoleId' => $this->userRoleId,
            'updatedAt' => $this->updatedAt?->format('Y-m-d H:i:s'),
            'createdAt' => $this->createdAt?->format('Y-m-d H:i:s'),
        ];
    }
}
