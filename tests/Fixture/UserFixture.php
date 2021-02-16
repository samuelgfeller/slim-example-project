<?php

namespace App\Test\Fixture;

/**
 * User values that can be inserted into the database
 */
class UserFixture
{
    // Table name
    public string $table = 'user';

    // Database records in 2d array
    public array $records = [
        [
            'id' => 1,
            'name' => 'Admin Example',
            'email' => 'admin@example.com',
            'password_hash' => '$2y$10$gmKq.1.ENGGdDdpj7Lgq8et9eAR16QD9eCvlahnx3IWOm.JJ/VWFi',
            'role' => 'admin',
            'updated_at' => null,
            'created_at' => '2021-01-01 00:00:01',
            'deleted_at' => null,
        ],
        [
            'id' => 2,
            'name' => 'User Example',
            'email' => 'user@example.com',
            'password_hash' => '$2y$10$gmKq.1.ENGGdDdpj7Lgq8et9eAR16QD9eCvlahnx3IWOm.JJ/VWFi',
            'role' => 'admin',
            'updated_at' => null,
            'created_at' => '2021-02-01 00:00:01',
            'deleted_at' => null,
        ],
    ];
}
