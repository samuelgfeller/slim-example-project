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
        // Post id 1 owner: user id 1
        [
            'id' => 1,
            'user_id' => 1,
            'message' => 'This is a test post',
            'updated_at' => null,
            'created_at' => '2021-01-01 00:00:05',
            'deleted_at' => null,
        ],
        // Post id 2 owner: user id 2
        [
            'id' => 2,
            'user_id' => 2,
            'message' => 'This is another test post',
            'updated_at' => null,
            'created_at' => '2021-01-01 00:00:01',
            'deleted_at' => null,
        ],
        [
            'id' => 3,
            'user_id' => 1,
            'message' => 'This is the second post referenced to user 1',
            'updated_at' => null,
            'created_at' => '2021-01-01 00:00:05',
            'deleted_at' => null,
        ],
    ];
}
