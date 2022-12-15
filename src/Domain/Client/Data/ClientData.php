<?php

namespace App\Domain\Client\Data;

use App\Domain\Client\Enum\ClientVigilanceLevel;

class ClientData implements \JsonSerializable
{
    public ?int $id;
    // Optional values have to be init with null as they are used even when not set in client-read template
    public ?string $firstName = null;
    public ?string $lastName = null;
    // DateTimeImmutable to not change original reference when modified
    public ?\DateTimeImmutable $birthdate = null;
    public ?string $location = null;
    public ?string $phone = null;
    public ?string $email = null;
    // https://ocelot.ca/blog/blog/2013/09/16/representing-sex-in-databases/
    public ?string $sex = null; // ENUM 'F' -> Female; 'M' -> Male; 'O' -> Other; NULL -> Not applicable.
    public ?string $clientMessage = null; // Message that client submitted via webform
    public ?ClientVigilanceLevel $vigilanceLevel = null;
    public ?int $userId;
    public ?int $clientStatusId;
    public ?\DateTimeImmutable $updatedAt;
    public ?\DateTimeImmutable $createdAt;
    public ?\DateTimeImmutable $deletedAt;

    // Not database field but here so that age doesn't have to be calculated in view
    public ?int $age = null;

    /**
     * Client Data constructor.
     *
     * @param array|null $clientData
     *
     * @throws \Exception
     */
    public function __construct(?array $clientData = [])
    {
        $this->id = $clientData['id'] ?? null;
        $this->firstName = $clientData['first_name'] ?? null;
        $this->lastName = $clientData['last_name'] ?? null;
        $this->birthdate = $clientData['birthdate'] ?? null ? new \DateTimeImmutable($clientData['birthdate']) : null;
        $this->location = $clientData['location'] ?? null;
        $this->phone = $clientData['phone'] ?? null;
        $this->email = $clientData['email'] ?? null;
        $this->sex = $clientData['sex'] ?? null;
        $this->clientMessage = $clientData['client_message'] ?? null;
        $this->vigilanceLevel = $clientData['vigilance_level'] ?? null ?
            ClientVigilanceLevel::tryFrom($clientData['vigilance_level']) : null;
        $this->userId = $clientData['user_id'] ?? null;
        $this->clientStatusId = $clientData['client_status_id'] ?? null;
        $this->updatedAt = $clientData['updated_at'] ?? null ? new \DateTimeImmutable($clientData['updated_at']) : null;
        $this->createdAt = $clientData['created_at'] ?? null ? new \DateTimeImmutable($clientData['created_at']) : null;
        $this->deletedAt = $clientData['deleted_at'] ?? null ? new \DateTimeImmutable($clientData['deleted_at']) : null;

        if ($this->birthdate) {
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
            'vigilance_level' => $this->vigilanceLevel?->value,
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

    /**
     * Output for json_encode
     * camelCase according to Google recommendation https://stackoverflow.com/a/19287394/9013718.
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'birthdate' => $this->birthdate?->format('Y-m-d'),
            'location' => $this->location,
            'phone' => $this->phone,
            'email' => $this->email,
            'sex' => $this->sex,
            'clientMessage' => $this->clientMessage,
            'vigilanceLevel' => $this->vigilanceLevel?->value,
            'userId' => $this->userId,
            'clientStatusId' => $this->clientStatusId,
            'updatedAt' => $this->updatedAt?->format('Y-m-d H:i:s'),
            'createdAt' => $this->createdAt?->format('Y-m-d H:i:s'),
            'deletedAt' => $this->deletedAt?->format('Y-m-d H:i:s'),
            'age' => $this->age,
        ];
    }
}
