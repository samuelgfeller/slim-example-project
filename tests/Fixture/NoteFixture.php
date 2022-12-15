<?php

namespace App\Test\Fixture;

/**
 * Post values that can be inserted into the database
 * UserFixture HAS to be inserted first.
 */
class NoteFixture
{
    // Table name
    public string $table = 'note';

    // Database records in 2d array
    public array $records = [
        // First record must have deleted_at => null
        [
            'id' => 1,
            'user_id' => 10,
            'client_id' => 1,
            'message' => 'This is the first fixture note.',
            'is_main' => 1,
            'hidden' => null,
            'updated_at' => '2021-01-01 00:00:01',
            'created_at' => '2021-01-01 00:00:01',
            'deleted_at' => null,
        ],
        // Note id 2: is not main note and linked to user 10 and client 1
        [
            'id' => 2,
            'user_id' => 10,
            'client_id' => 1,
            'message' => 'This is the second fixture note.',
            'is_main' => 0,
            'hidden' => null,
            'updated_at' => '2021-01-01 00:00:01',
            'created_at' => '2021-01-01 00:00:01',
            'deleted_at' => null,
        ],
        // Note id 3
        [
            'id' => 3,
            'user_id' => 20,
            'client_id' => 1,
            'message' => 'This is the third fixture note.',
            'is_main' => 0,
            'hidden' => null,
            'updated_at' => '2021-01-01 00:00:01',
            'created_at' => '2021-01-01 00:00:01',
            'deleted_at' => null,
        ],
        // Note id 4
        [
            'id' => 4,
            'user_id' => 20,
            'client_id' => 1,
            'message' => 'This is the fourth fixture note.',
            'is_main' => 0,
            'hidden' => null,
            'updated_at' => '2021-01-01 00:00:01',
            'created_at' => '2021-01-01 00:00:01',
            'deleted_at' => null,
        ],

        // Note id 5: is not main note and linked to user 1 and IS DELETED
        [
            'id' => 5,
            'user_id' => 1,
            'client_id' => 1,
            'message' => 'This is the sixth fixture note.',
            'is_main' => 0,
            'hidden' => null,
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
            'hidden' => null,
            'updated_at' => '2021-01-01 00:00:01',
            'created_at' => '2021-01-01 00:00:01',
            'deleted_at' => null,
        ],
        // Note id 7: is not main note and linked to user 1
        [
            'id' => 7,
            'user_id' => 1,
            'client_id' => 2,
            'message' => 'This is a normal note.',
            'is_main' => 0,
            'hidden' => null,
            'updated_at' => '2021-01-01 00:00:01',
            'created_at' => '2021-01-01 00:00:01',
            'deleted_at' => null,
        ],
    ];
}
