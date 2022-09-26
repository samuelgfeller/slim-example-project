<?php

namespace App\Test\Fixture;

/**
 * Post values that can be inserted into the database
 * UserFixture HAS to be inserted first
 */
class NoteFixture
{
    // Table name
    public string $table = 'note';

    // Database records in 2d array
    public array $records = [
        // Client id 1
        // Note id 1: is main note and linked to user 1
        [
            'id' => 1,
            'user_id' => 1,
            'client_id' => 1,
            'message' => 'This is a test main note.',
            'is_main' => 1,
            'updated_at' => '2021-01-01 00:00:01',
            'created_at' => '2021-01-01 00:00:01',
            'deleted_at' => null,
        ],
        // Note id 2: is not main note and linked to user 1
        [
            'id' => 2,
            'user_id' => 1,
            'client_id' => 1,
            'message' => 'This is a normal note from the same user 1.',
            'is_main' => 0,
            'updated_at' => '2021-01-01 00:00:01',
            'created_at' => '2021-01-01 00:00:01',
            'deleted_at' => null,
        ],
        // Note id 3: is not main note and linked to user 2
        [
            'id' => 3,
            'user_id' => 2,
            'client_id' => 1,
            'message' => 'This is a normal note from the user 2.',
            'is_main' => 0,
            'updated_at' => '2021-01-01 00:00:01',
            'created_at' => '2021-01-01 00:00:01',
            'deleted_at' => null,
        ],
        // Note id 4: is not main note and linked to non admin user 2
        [
            'id' => 4,
            'user_id' => 2,
            'client_id' => 1,
            'message' => 'This is a second normal note from the user 2.',
            'is_main' => 0,
            'updated_at' => '2021-01-01 00:00:01',
            'created_at' => '2021-01-01 00:00:01',
            'deleted_at' => null,
        ],

        // Note id 5: is not main note and linked to user 1 and IS DELETED
        [
            'id' => 5,
            'user_id' => 1,
            'client_id' => 1,
            'message' => 'This is a deleted note attached to user 1.',
            'is_main' => 0,
            'updated_at' => '2021-01-01 00:00:01',
            'created_at' => '2021-01-01 00:00:01',
            'deleted_at' => '2021-01-01 00:00:02',
        ],

        // Client id 2
        // Note id 6: is the main note for client 2 and created by user 1
        [
            'id' => 6,
            'user_id' => 1,
            'client_id' => 2,
            'message' => 'This is a test main note.',
            'is_main' => 1,
            'updated_at' => '2021-01-01 00:00:01',
            'created_at' => '2021-01-01 00:00:01',
            'deleted_at' => null,
        ],
        // Note id 7: is not main note and linked to user 1
        [
            'id' => 7,
            'user_id' => 1,
            'client_id' => 2,
            'message' => 'This is a normal note from the same user 1.',
            'is_main' => 0,
            'updated_at' => '2021-01-01 00:00:01',
            'created_at' => '2021-01-01 00:00:01',
            'deleted_at' => null,
        ],
    ];
}
