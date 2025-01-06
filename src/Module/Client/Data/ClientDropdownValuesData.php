<?php

namespace App\Module\Client\Data;

use App\Module\Client\Enum\ClientVigilanceLevel;
use App\Module\General\Enum\SexOption;

/**
 * All status, sex and users for dropdowns.
 */
class ClientDropdownValuesData
{
    // [int:id => string:name]
    public ?array $statuses;
    public ?array $users;
    public ?array $sexes;
    public ?array $vigilanceLevel;

    public function __construct(
        ?array $statuses = null,
        ?array $users = null,
    ) {
        $this->statuses = $statuses;
        $this->users = $users;
        $this->sexes = SexOption::getAllDisplayNames();
        $this->vigilanceLevel = ClientVigilanceLevel::getAllDisplayNames();
    }
}
