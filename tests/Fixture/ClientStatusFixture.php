<?php

namespace App\Test\Fixture;

/**
 * Client status values that can be inserted into the database
 * No fixture has to be inserted first.
 */
class ClientStatusFixture
{
    // Table name
    public string $table = 'client_status';

    // Database records in 2d array
    public array $records = [
        // First record must have deleted_at => null
        [
            'id' => 1,
            'name' => 'Action pending',
            'deleted_at' => null,
        ],
        [
            'id' => 2,
            'name' => 'Done',
            'deleted_at' => null,
        ],
    ];
}
