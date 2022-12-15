<?php

namespace App\Test\Fixture;

/**
 * User role fixture.
 */
class UserRoleFixture
{
    // Table name
    public string $table = 'user_role';

    // Database records in 2d array
    public array $records = [
        [
            'id' => 1,
            'name' => 'admin',
            'hierarchy' => 1,
        ],
        [
            'id' => 2,
            'name' => 'managing_advisor',
            'hierarchy' => 2,
        ],
        [
            'id' => 3,
            'name' => 'advisor',
            'hierarchy' => 3,
        ],
        [
            'id' => 4,
            'name' => 'newcomer',
            'hierarchy' => 4,
        ],
    ];
}
