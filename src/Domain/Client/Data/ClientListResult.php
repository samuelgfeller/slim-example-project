<?php

namespace App\Domain\Client\Data;

use App\Domain\Authorization\Privilege;

/**
 * Aggregate DTO to store ClientData combined with
 * client status and assigned user privileges.
 */
class ClientListResult extends ClientData
{
    public ?Privilege $clientStatusPrivilege = null;
    public ?Privilege $assignedUserPrivilege = null;

    public function __construct(array $clientResultData = [])
    {
        parent::__construct($clientResultData);
    }

    /**
     * Output for json_encode.
     */
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'clientStatusPrivilege' => $this->clientStatusPrivilege?->value,
            'assignedUserPrivilege' => $this->assignedUserPrivilege?->value,
        ]);
    }
    // No need for toArrayForDatabase() as this is a result DTO
}
