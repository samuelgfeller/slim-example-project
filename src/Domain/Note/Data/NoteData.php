<?php

namespace App\Domain\Note\Data;

use App\Domain\Authorization\Privilege;
use App\Domain\User\Data\UserData;
use IntlDateFormatter;

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
    public Privilege $privilege = Privilege::NONE; // json_encode automatically takes $enum->value

    public function __construct(?array $noteValues = null)
    {
        $this->id = $noteValues['id'] ?? null;
        $this->userId = $noteValues['user_id'] ?? null;
        $this->clientId = $noteValues['client_id'] ?? null;
        $this->message = $noteValues['message'] ?? null;
        $this->isMain = $noteValues['is_main'] ?? null;
        $this->hidden = $noteValues['hidden'] ?? null;
        $this->createdAt = $noteValues['created_at'] ?? null
            ? new \DateTimeImmutable($noteValues['created_at']) : null;
        $this->updatedAt = $noteValues['updated_at'] ?? null
            ? new \DateTimeImmutable($noteValues['updated_at']) : null;
        $this->deletedAt = $noteValues['deleted_at'] ?? null
            ? new \DateTimeImmutable($noteValues['deleted_at']) : null;
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
        $dateFormatter = new IntlDateFormatter(
            setlocale(LC_ALL, 0) ?: null,
            IntlDateFormatter::LONG,
            IntlDateFormatter::SHORT
        );

        return [
            'id' => $this->id,
            'userId' => $this->userId,
            'clientId' => $this->clientId,
            'message' => $this->message,
            // 'isMain' => $this->isMain,
            'hidden' => $this->hidden,
            'createdAt' => $this->createdAt ? $dateFormatter->format($this->createdAt) : null,
            'updatedAt' => $this->updatedAt ? $dateFormatter->format($this->updatedAt) : null,
        ];
    }
}
