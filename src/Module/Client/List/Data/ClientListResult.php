<?php

namespace App\Module\Client\List\Data;

use App\Module\Client\Data\ClientData;

/**
 * Aggregate DTO to store ClientData combined with
 * client status and assigned user privileges.
 */
class ClientListResult extends ClientData
{
    public ?string $clientStatusPrivilege = null;
    public ?string $assignedUserPrivilege = null;

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
            'clientStatusPrivilege' => $this->clientStatusPrivilege,
            'assignedUserPrivilege' => $this->assignedUserPrivilege,
        ]);
    }
}
