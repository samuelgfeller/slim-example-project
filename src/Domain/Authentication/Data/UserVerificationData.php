<?php

namespace App\Domain\Authentication\Data;

use App\Common\ArrayReader;

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
        $arrayReader = new ArrayReader($verificationData);
        // ArrayReader find*() casts the values in the given format
        $this->id = $arrayReader->findAsInt('id');
        $this->userId = $arrayReader->findAsInt('user_id');
        $this->token = $arrayReader->findAsString('token');
        $this->expiresAt = $arrayReader->findAsString('expires_at');
        $this->usedAt = $arrayReader->findAsString('used_at');
        $this->createdAt = $arrayReader->findAsString('created_at');
    }

    /**
     * Returns values of object as array for database
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
