<?php

namespace App\Domain\Client\Data;


use App\Common\ArrayReader;
use App\Common\DateTimeImmutable;
use App\Domain\Note\Data\NoteData;

class ClientData
{

    public ?int $id;
    public ?string $firstName;
    public ?string $lastName;
    // DateTimeImmutable to not change original reference when modified
    public ?DateTimeImmutable $birthdate;
    public ?string $location;
    public ?string $phone;
    public ?string $email;
    // https://ocelot.ca/blog/blog/2013/09/16/representing-sex-in-databases/
    public ?string $sex; // ENUM 'F' -> Female; 'M' -> Male; 'O' -> Other; NULL -> Not applicable.
    public ?string $clientMessage; // Message that client submitted via webform
    public ?int $userId;
    public ?int $clientStatusId;
    public ?DateTimeImmutable $updatedAt;
    public ?DateTimeImmutable $createdAt;

    // Not database field but here so that age doesn't have to be calculated in view
    public ?int $age;

    /**
     * Client Data constructor.
     * @param array|null $clientData
     */
    public function __construct(?array $clientData = [])
    {
        $reader = new ArrayReader($clientData);
        $this->id = $reader->findAsInt('id');
        $this->firstName = $reader->findAsString('first_name');
        $this->lastName = $reader->findAsString('last_name');
        $this->birthdate = $reader->findAsDateTimeImmutable('birthdate');
        $this->location = $reader->findAsString('location');
        $this->phone = $reader->findAsString('phone');
        $this->email = $reader->findAsString('email');
        $this->sex = $reader->findAsString('sex');
        $this->clientMessage = $reader->findAsString('client_message');
        $this->userId = $reader->findAsInt('user_id');
        $this->clientStatusId = $reader->findAsInt('client_status_id');
        $this->updatedAt = $reader->findAsDateTimeImmutable('updated_at');
        $this->createdAt = $reader->findAsDateTimeImmutable('created_at');

        if ($this->birthdate){
            $this->age = (new \DateTime())->diff($this->birthdate)->y;
        }
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
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            // If birthdate not null, return given format
            'birthdate' => $this->birthdate?->format('Y-m-d'),
            'location' => $this->location,
            'phone' => $this->phone,
            'email' => $this->email,
            'sex' => $this->sex,
            'client_message' => $this->clientMessage,
            'user_id' => $this->userId,
            'client_status_id' => $this->clientStatusId,
        ];

        // Not include required, from db non-nullable values if they are null -> for update
        // Needed for update but if value null it is not included in return array as it's a non-nullable AUTO_INCREMENT col
        if ($this->id !== null) {
            $clientArray['id'] = $this->id;
        }

        return $clientArray;
    }
}