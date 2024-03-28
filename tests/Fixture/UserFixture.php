<?php

namespace App\Test\Fixture;

use TestTraits\Interface\FixtureInterface;

/**
 * User values that can be inserted into the database.
 * ! All user roles are inserted automatically for each test (in AppTestTrait).
 */
class UserFixture implements FixtureInterface
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
            'theme' => 'light',
            'language' => 'en_US',
            // 'status' => UserStatus::ACTIVE->value,
            'updated_at' => '2021-01-01 00:00:01',
            'created_at' => '2021-01-01 00:00:01',
            'deleted_at' => null,
        ],
    ];

    public function getTable(): string
    {
        return $this->table;
    }

    public function getRecords(): array
    {
        return $this->records;
    }
}
