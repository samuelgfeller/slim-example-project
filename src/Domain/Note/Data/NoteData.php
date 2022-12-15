<?php

namespace App\Domain\Note\Data;

use App\Domain\Authorization\Privilege;
use App\Domain\User\Data\UserData;

class NoteData implements \JsonSerializable
{
    public ?int $id;
    public ?int $userId;
    public ?int $clientId;
    public ?string $message;
    public ?int $isMain; // int 1 or 0
    public ?int $hidden; // int 1 or 0
    public ?\DateTimeImmutable $createdAt;
    public ?\DateTimeImmutable $updatedAt;
    public ?\DateTimeImmutable $deletedAt;

    // Not in database
    public ?UserData $user;

    // User mutation rights from authenticated user
    public ?Privilege $privilege; // json_encode automatically takes $enum->value

    /**
     * Note constructor.
     *
     * @param array|null $noteResultData
     *
     * @throws \Exception
     */
    public function __construct(?array $noteResultData = null)
    {
        $this->id = $noteResultData['id'] ?? null;
        $this->userId = $noteResultData['user_id'] ?? null;
        $this->clientId = $noteResultData['client_id'] ?? null;
        $this->message = $noteResultData['message'] ?? null;
        $this->isMain = $noteResultData['is_main'] ?? null;
        $this->hidden = $noteResultData['hidden'] ?? null;
        $this->createdAt = $noteResultData['created_at'] ?? null
            ? new \DateTimeImmutable($noteResultData['created_at']) : null;
        $this->updatedAt = $noteResultData['updated_at'] ?? null
            ? new \DateTimeImmutable($noteResultData['updated_at']) : null;
        $this->deletedAt = $noteResultData['deleted_at'] ?? null
            ? new \DateTimeImmutable($noteResultData['deleted_at']) : null;
    }

    /**
     * Returns all values of object as array.
     * The array keys should match with the database
     * column names since it is likely used to
     * modify a database table.
     *
     * @return array
     */
    public function toArray(): array
    {
        // Not include required, from db non-nullable values if they are null -> for update
        if ($this->id !== null) {
            $note['id'] = $this->id;
        }
        if ($this->userId !== null) {
            $note['user_id'] = $this->userId;
        }
        if ($this->clientId !== null) {
            $note['client_id'] = $this->clientId;
        }

        // Message is nullable and null is a valid value, so it has to be included
        $note['message'] = $this->message;
        $note['is_main'] = $this->isMain;
        $note['hidden'] = $this->hidden;

        return $note;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->userId,
            'clientId' => $this->clientId,
            'message' => $this->message,
            // 'isMain' => $this->isMain,
            'hidden' => $this->hidden,
            // F is the full month name in english
            'createdAt' => $this->createdAt?->format('d. F Y • H:i'),
            'updatedAt' => $this->updatedAt?->format('d. F Y • H:i'),
            // 'deletedAt' => $this->deletedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
