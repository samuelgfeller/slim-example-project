<?php

namespace App\Domain\Client\Data;


use App\Domain\ClientStatus\Data\ClientStatusData;
use App\Domain\Note\Data\NoteData;
use App\Domain\User\Data\UserData;

/**
 * Aggregate DTO to store ClientData combined with
 * some linked (aggregate) classes.
 * Used as result DTO when access to aggregate
 * details is relevant.
 */
class ClientResultAggregateData extends ClientData
{

    public ?ClientStatusData $clientStatusData;
    public ?UserData $userData;
    /** @var NoteData[]|null $notes */
    public ?array $notes = null;

    /**
     * Client Data constructor.
     * @param array|null $clientResultData
     */
    public function __construct(array $clientResultData = null)
    {
        parent::__construct($clientResultData);

        // Aggregate DTOs populated with values relevant to client result
        $this->clientStatusData = new ClientStatusData([
            'name' => $clientResultData['status_name'] ?? null
        ]);
        // User data populated with values relevant to client result
        $this->userData = new UserData([
            'first_name' => $clientResultData['user_first_name'] ?? null,
            'surname' => $clientResultData['user_surname'] ?? null,
        ]);
    }

    // No need for toArrayForDatabase() as this is a result DTO
}