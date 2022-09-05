<?php

namespace App\Domain\Client\Data;


use App\Common\ArrayReader;

class ClientData
{

    public ?int $id;
    public ?string $first_name;
    public ?string $last_name;
    public ?\DateTimeImmutable $birthdate; // DateTimeImmutable to not change original reference when modified
    public ?string $location;
    public ?string $phone;
    public ?string $email;
    // https://ocelot.ca/blog/blog/2013/09/16/representing-sex-in-databases/
    public ?string $sex; // ENUM 'F' -> Female; 'M' -> Male; 'O' -> Other; NULL -> Not applicable.
    public ?string $client_message; // Message that client submitted via webform
    public ?int $user_id;
    public ?int $client_status_id;
    public ?int $note_id; /* Main note */
    public ?\DateTimeImmutable $updated_at;
    public ?\DateTimeImmutable $created_at;

    // Not database field but here so that age doesn't have to be calculated in view
    public ?int $age;

    /**
     * Client Data constructor.
     * @param array|null $clientData
     */
    public function __construct(array $clientData = null)
    {
        $reader = new ArrayReader($clientData);
        $this->id = $reader->findAsInt('id');
        $this->first_name = $reader->findAsString('first_name');
        $this->last_name = $reader->findAsString('last_name');
        $this->birthdate = $reader->findAsDateTimeImmutable('birthdate');
        $this->location = $reader->findAsString('location');
        $this->phone = $reader->findAsString('phone');
        $this->email = $reader->findAsString('email');
        $this->sex = $reader->findAsString('sex');
        $this->client_message = $reader->findAsString('client_message');
        $this->user_id = $reader->findAsInt('user_id');
        $this->client_status_id = $reader->findAsInt('client_status_id');
        $this->note_id = $reader->findAsInt('client_status_id');
        $this->updated_at = $reader->findAsDateTimeImmutable('updated_at');
        $this->created_at = $reader->findAsDateTimeImmutable('created_at');

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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'birthdate' => $this->birthdate,
            'location' => $this->location,
            'phone' => $this->phone,
            'email' => $this->email,
            'note' => $this->note,
            'sex' => $this->sex,
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