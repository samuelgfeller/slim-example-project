<?php

namespace App\Domain\Client\Data;

use App\Domain\Client\Enum\ClientVigilanceLevel;
use App\Domain\General\Enum\SexOption;

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
        $this->sexes = SexOption::toArrayWithPrettyNames();
        $this->vigilanceLevel = ClientVigilanceLevel::toArrayWithPrettyNames();
    }
}
