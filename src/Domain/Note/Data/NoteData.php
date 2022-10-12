<?php

namespace App\Domain\Note\Data;


use App\Common\ArrayReader;
use App\Domain\User\Data\UserData;

class NoteData
{
    public ?int $id;
    public ?int $userId;
    public ?int $clientId;
    public ?string $message;
    public ?int $isMain;
    public ?string $createdAt;
    public ?string $updatedAt;
    public ?string $deletedAt;
    public ?UserData $user;

    /**
     * Note constructor.
     * @param array|null $noteData
     */
    public function __construct(?array $noteData = null)
    {
        $arrayReader = new ArrayReader($noteData);
        $this->id = $arrayReader->findAsInt('id');
        $this->userId = $arrayReader->findAsInt('user_id');
        $this->clientId = $arrayReader->findAsInt('client_id');
        $this->message = $arrayReader->findAsString('message');
        $this->isMain = $arrayReader->findAsInt('is_main'); // Not sure if int or bool is better in this situation
        $this->createdAt = $arrayReader->findAsString('created_at');
        $this->updatedAt = $arrayReader->findAsString('updated_at');
        $this->deletedAt = $arrayReader->findAsString('deleted_at');
    }

    /**
     * Returns all values of object as array.
     * The array keys should match with the database
     * column names since it is likely used to
     * modify a database table
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


        // Message is nullable and null is a valid value so it has to be included todo detect null values and add IS for cakequery builder IS NULL
        $note['message'] = $this->message;

        $note['is_main'] = $this->isMain;

        return $note;
    }
}