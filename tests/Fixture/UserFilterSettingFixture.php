<?php

namespace App\Test\Fixture;

use TestTraits\Interface\FixtureInterface;

class UserFilterSettingFixture implements FixtureInterface
{
    // Table name
    public string $table = 'user_filter_setting';

    // Database records in 2d array
    public array $records = [
        [
            'user_id' => 1,
            'filter_id' => 1,
            'module' => 'dashboard-panel',
        ],
    ];

    public function getTable(): string
    {
        return $this->table;
    }

    public function getRecords(): array
    {
        return $this->records;
    }
}
