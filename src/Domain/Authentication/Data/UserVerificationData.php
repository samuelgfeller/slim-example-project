<?php

namespace App\Domain\Authentication\Data;

/**
 * Class User also serving as DTO for simplicity reasons. More details on slim-api-example/issues/2
 * Public attributes: Basically if it is intended to interface DTOs or there may be read-only fields it makes
 * sense to keep them private otherwise not really.
 */
class UserVerificationData
{
    public ?int $id;
    public ?int $userId;
    public ?string $token;
    public ?int $expiresAt;
    public ?string $usedAt = null;
    public ?string $createdAt;

    /**
     * @param array $verificationData
     */
    public function __construct(array $verificationData = [])
    {
        $this->id = $verificationData['id'] ?? null;
        $this->userId = $verificationData['user_id'] ?? null;
        $this->token = $verificationData['token'] ?? null;
        $this->expiresAt = $verificationData['expires_at'] ?? null;
        $this->usedAt = $verificationData['used_at'] ?? null;
        $this->createdAt = $verificationData['created_at'] ?? null;
    }

    /**
     * Returns values of object as array for database.
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
            'user_id' => $this->userId,
            'token' => $this->token,
            'expires_at' => $this->expiresAt,
            'used_at' => $this->usedAt,
            'created_at' => $this->createdAt,
        ];
    }
}
