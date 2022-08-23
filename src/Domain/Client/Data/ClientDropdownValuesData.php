<?php

namespace App\Domain\Client\Data;

/**
 * All status, sex and users for dropdowns
 */
class ClientDropdownValuesData
{
    // [int:id => string:name]
    public ?array $statuses;
    public ?array $users;
    public ?array $sexes = ['M' => 'Male', 'F' => 'Female', 'O' => 'Other', 'NULL' => null];

    public function __construct(
        ?array $statuses = null,
        ?array $users = null,
        ?array $sexes = null,
    )
    {
        $this->statuses = $statuses;
        $this->users = $users;
        $this->sexes = $sexes;
    }
}