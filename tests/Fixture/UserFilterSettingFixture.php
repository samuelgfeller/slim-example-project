<?php

namespace App\Test\Fixture;

class UserFilterSettingFixture
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
}
