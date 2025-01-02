<?php

namespace App\Modules\Client\Data;

use App\Modules\Client\Enum\ClientVigilanceLevel;
use App\Modules\General\Enum\SexOption;

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
