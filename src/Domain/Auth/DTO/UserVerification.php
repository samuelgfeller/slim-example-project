<?php

namespace App\Domain\Auth\DTO;

use App\Domain\Utility\ArrayReader;

/**
 * Class User also serving as DTO for simplicity reasons. More details on slim-api-example/issues/2
 * Public attributes: Basically if it is intended to interface DTOs or there may be read-only fields it makes
 * sense to keep them private otherwise not really.
 *
 */
class UserVerification
{
    public ?int $id;
    public ?int $userId;
    public ?string $token;
    public ?int $expires;
    public ?string $usedAt;
    public ?string $createdAt;

    /**
     * User constructor.
     * @param array $userData
     */
    public function __construct(array $userData = [])
    {
        $arrayReader = new ArrayReader($userData);
        // ArrayReader find*() casts the values in the given format
        $this->id = $arrayReader->findInt('id');
        $this->userId = $arrayReader->findInt('user_id');
        $this->token = $arrayReader->findString('token');
        $this->expires = $arrayReader->findInt('expires');
        $this->createdAt = $arrayReader->findString('created_at');
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
            'expires' => $this->expires,
            'created_at' => $this->createdAt,
        ];
    }

}
