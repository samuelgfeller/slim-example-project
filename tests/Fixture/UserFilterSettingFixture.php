<?php

namespace App\Test\Fixture;

/**
 * Post values that can be inserted into the database
 * UserFixture HAS to be inserted first.
 */
class UserFilterSettingFixture
{
    // Table name
    public string $table = 'user_filter_setting';

    // Database records in 2d array
    public array $records = [
        [
            'user_id' => 1,
            'filer_id' => 1,
            'module' => 'dashboard-panel',
        ],
    ];
}
