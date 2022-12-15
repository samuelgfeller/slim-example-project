<?php

namespace App\Domain\Client\Data;

class ClientResultDataCollection
{
    // Collection of clients
    /** @var ClientResultData[]|null */
    public ?array $clients = [];

    // This is a result data class and is transmitted to the view that needs all status, sex and users for dropdowns
    public ?array $statuses;
    public ?array $users;
    public ?array $sexes = ['M' => 'Male', 'F' => 'Female', 'O' => 'Other'];
}
