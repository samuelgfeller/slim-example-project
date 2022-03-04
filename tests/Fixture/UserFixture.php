<?php

namespace App\Test\Fixture;

use App\Domain\User\Data\UserData;

/**
 * User values that can be inserted into the database
 */
class UserFixture
{
    // Table name
    public string $table = 'user';

    // Database records in 2d array
    public array $records = [
        // User id 1: role admin
        [
            'id' => 1,
            'first_name' => 'Admin',
            'surname' => 'Example',
            'email' => 'admin@example.com',
            // Cleartext password is 12345678
            'password_hash' => '$2y$10$r8t5LRX7Hq1.22/h6dwe1uLrrhZnGTOnsue5p/rUgeD8BAhDwFhk2',
            'role' => 'admin',
            'status' => UserData::STATUS_ACTIVE,
            'updated_at' => null,
            'created_at' => '2021-01-01 00:00:01',
            'deleted_at' => null,
        ],
        // User id 2: role user
        [
            'id' => 2,
            'first_name' => 'User',
            'surname' => 'Example',
            'email' => 'user@example.com',
            'password_hash' => '$2y$10$G42IQACXblpLSoVez77qjeRBS./junh4X3.zdZeuAxJbKZGhfvymC',
            'role' => 'user',
            'status' => UserData::STATUS_ACTIVE,
            'updated_at' => null,
            'created_at' => '2021-02-01 00:00:01',
            'deleted_at' => null,
        ],
    ];
}
