<?php

namespace App\Test\Fixture;

/**
 * Post values that can be inserted into the database
 * UserFixture HAS to be inserted first
 */
class PostFixture
{
    // Table name
    public string $table = 'post';

    // Database records in 2d array
    public array $records = [
        [
            'id' => 1,
            'user_id' => 1,
            'message' => 'This is a test post',
            'updated_at' => null,
            'created_at' => '2021-01-01 00:00:05',
            'deleted_at' => null,
        ],
        [
            'id' => 2,
            'user_id' => 2,
            'message' => 'This is another test post',
            'updated_at' => null,
            'created_at' => '2021-01-01 00:00:01',
            'deleted_at' => null,
        ],

    ];
}
