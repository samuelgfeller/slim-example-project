<?php

namespace App\Domain\Client\Data;


use App\Common\ArrayReader;
use App\Domain\User\Data\UserData;

class ClientData
{

    public ?int $id;
    public ?string $first_name;
    public ?string $last_name;
    public ?\DateTimeImmutable $birthdate; // DateTimeImmutable to not change original reference when modified
    public ?string $location;
    public ?string $phone;
    public ?string $email;
    public ?string $note;
    public ?int $user_id;
    public ?int $client_status_id;
    public ?\DateTimeImmutable $updated_at;
    public ?\DateTimeImmutable $created_at;

    /**
     * Client Data constructor.
     * @param array|null $clientData
     */
    public function __construct(array $clientData = null)
    {
        $reader = new ArrayReader($clientData);
        $this->id = $reader->findAsInt($clientData['id']);
        $this->first_name = $reader->findAsString($clientData['first_name']);
        $this->last_name = $reader->findAsString($clientData['last_name']);
        $this->birthdate = $reader->findAsDateTimeImmutable($clientData['birthdate']);
        $this->location = $reader->findAsString($clientData['location']);
        $this->phone = $reader->findAsString($clientData['phone']);
        $this->email = $reader->findAsString($clientData['email']);
        $this->note = $reader->findAsString($clientData['note']);
        $this->user_id = $reader->findAsInt($clientData['user_id']);
        $this->client_status_id = $reader->findAsInt($clientData['client_status_id']);
        $this->updated_at = $reader->findAsDateTimeImmutable($clientData['updated_at']);
        $this->created_at = $reader->findAsDateTimeImmutable($clientData['created_at']);
    }

    /**
     * Returns all values of object as array for the database.
     * The array keys should match with the database
     * column names.
     *
     * @return array
     */
    public function toArrayForDatabase(): array
    {
        $clientArray = [
            // id set below
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'birthdate' => $this->birthdate,
            'location' => $this->location,
            'phone' => $this->phone,
            'email' => $this->email,
            'note' => $this->note,
            'user_id' => $this->user_id,
            'client_status_id' => $this->client_status_id,
        ];

        // Not include required, from db non-nullable values if they are null -> for update
        // Needed for update but if value null it is not included in return array as it's a non-nullable AUTO_INCREMENT col
        if ($this->id !== null) {
            $clientArray['id'] = $this->id;
        }

        return $clientArray;
    }
}