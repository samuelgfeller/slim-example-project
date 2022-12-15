<?php

namespace App\Test\Fixture;

/**
 * User values that can be inserted into the database
 * * All user roles are inserted automatically for each test (in AppTestTrait).
 */
class UserFixture
{
    // Table name
    public string $table = 'user';

    // Database records in 2d array
    public array $records = [
        // First user MUST not be deleted
        [
            'id' => 1,
            'first_name' => 'Example',
            'surname' => 'User',
            'email' => 'user@example.com',
            // Cleartext password is 12345678
            'password_hash' => '$2y$10$r8t5LRX7Hq1.22/h6dwe1uLrrhZnGTOnsue5p/rUgeD8BAhDwFhk2',
            'user_role_id' => 1,
            'status' => 'active',
            // 'status' => UserStatus::ACTIVE->value, // Only possible after 8.2 and there is no xampp version yet
            'updated_at' => '2021-01-01 00:00:01',
            'created_at' => '2021-01-01 00:00:01',
            'deleted_at' => null,
        ],
        // Second admin
        [
            'id' => 2,
            'first_name' => 'Second',
            'surname' => 'User',
            'email' => 'user2@example.com',
            // Cleartext password is 12345678
            'password_hash' => '$2y$10$r8t5LRX7Hq1.22/h6dwe1u2LrrhZnGTOnsue5p/rUgeD8BAhDwFhk2',
            'user_role_id' => 1,
            'status' => 'active',
            'updated_at' => '2021-01-01 00:00:01',
            'created_at' => '2021-01-01 00:00:01',
            'deleted_at' => null,
        ],
        // Managing advisor
        [
            'id' => 10,
            'first_name' => 'Managing',
            'surname' => 'Advisor',
            'email' => 'manager@example.com',
            // Password is 12345678
            'password_hash' => '$2y$10$G42IQACXblpLSoVez77qjeRBS./junh4X3.zdZeuAxJbKZGhfvymC',
            'user_role_id' => 2,
            'status' => 'active',
            'updated_at' => '2021-01-01 00:00:01',
            'created_at' => '2021-01-01 00:00:01',
            'deleted_at' => null,
        ],
        // Second managing advisor
        [
            'id' => 11,
            'first_name' => 'Second',
            'surname' => 'Manager',
            'email' => 'manager2@example.com',
            // Password is 12345678
            'password_hash' => '$2y$10$G42IQACXblpLSoVez77qjeRBS./junh4X3.zdZeuAxJbKZGhfvymC',
            'user_role_id' => 2,
            'status' => 'active',
            'updated_at' => '2021-01-01 00:00:01',
            'created_at' => '2021-01-01 00:00:01',
            'deleted_at' => null,
        ],
        // Third deleted managing advisor
        [
            'id' => 12,
            'first_name' => 'Second',
            'surname' => 'Manager',
            'email' => 'manager2@example.com',
            // Password is 12345678
            'password_hash' => '$2y$10$G42IQACXblpLSoVez77qjeRBS./junh4X3.zdZeuAxJbKZGhfvymC',
            'user_role_id' => 2,
            'status' => 'active',
            'updated_at' => '2021-01-01 00:00:01',
            'created_at' => '2021-01-01 00:00:01',
            'deleted_at' => '2021-01-01 00:00:02',
        ],

        // Advisor
        [
            'id' => 20,
            'first_name' => 'Advisor',
            'surname' => 'Example',
            'email' => 'advisor@example.com',
            // Password is 12345678
            'password_hash' => '$2y$10$G42IQACXblpLSoVez77qjeRBS./junh4X3.zdZeuAxJbKZGhfvymC',
            'user_role_id' => 3,
            'status' => 'active',
            'updated_at' => '2021-01-01 00:00:01',
            'created_at' => '2021-01-01 00:00:01',
            'deleted_at' => null,
        ],
        // Second advisor
        [
            'id' => 21,
            'first_name' => 'Second',
            'surname' => 'Advisor',
            'email' => 'advisor2@example.com',
            // Password is 12345678
            'password_hash' => '$2y$10$G42IQACXblpLSoVez77qjeRBS./junh4X3.zdZeuAxJbKZGhfvymC',
            'user_role_id' => 3,
            'status' => 'active',
            'updated_at' => '2021-01-01 00:00:01',
            'created_at' => '2021-01-01 00:00:01',
            'deleted_at' => null,
        ],
        // Third deleted advisor
        [
            'id' => 22,
            'first_name' => 'Third',
            'surname' => 'Advisor',
            'email' => 'advisor3@example.com',
            // Password is 12345678
            'password_hash' => '$2y$10$G42IQACXblpLSoVez77qjeRBS./junh4X3.zdZeuAxJbKZGhfvymC',
            'user_role_id' => 3,
            'status' => 'active',
            'updated_at' => '2021-01-01 00:00:01',
            'created_at' => '2021-01-01 00:00:01',
            'deleted_at' => '2021-01-01 00:00:02',
        ],

        // First newcomer
        [
            'id' => 30,
            'first_name' => 'Newcomer',
            'surname' => 'Example',
            'email' => 'newcomer@example.com',
            // Password is 12345678
            'password_hash' => '$2y$10$G42IQACXblpLSoVez77qjeRBS./junh4X3.zdZeuAxJbKZGhfvymC',
            'user_role_id' => 4,
            'status' => 'active',
            'updated_at' => '2021-01-01 00:00:01',
            'created_at' => '2021-01-01 00:00:01',
            'deleted_at' => null,
        ],
        // Second newcomer
        [
            'id' => 31,
            'first_name' => 'Second',
            'surname' => 'Newcomer',
            'email' => 'newcomer2@example.com',
            // Password is 12345678
            'password_hash' => '$2y$10$G42IQACXblpLSoVez77qjeRBS./junh4X3.zdZeuAxJbKZGhfvymC',
            'user_role_id' => 4,
            'status' => 'active',
            'updated_at' => '2021-01-01 00:00:01',
            'created_at' => '2021-01-01 00:00:01',
            'deleted_at' => null,
        ],

        // Deleted newcomer
        [
            'id' => 32,
            'first_name' => 'Newcomer',
            'surname' => 'Deleted',
            'email' => 'newcomer3@example.com',
            // Password is 12345678
            'password_hash' => '$2y$10$G42IQACXblpLSoVez77qjeRBS./junh4X3.zdZeuAxJbKZGhfvymC',
            'user_role_id' => 4,
            'status' => 'active',
            'updated_at' => '2021-01-01 00:00:01',
            'created_at' => '2021-01-01 00:00:01',
            'deleted_at' => '2021-01-01 00:00:02',
        ],
    ];
}
