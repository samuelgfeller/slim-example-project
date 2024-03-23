<?php

namespace App\Test\Fixture;

use TestTraits\Interface\FixtureInterface;

class UserActivityFixture implements FixtureInterface
{
    // Table name
    public string $table = 'user_activity';

    // Database records in 2d array
    public array $records = [
        [
            'id' => 1,
            'user_id' => 1,
            'action' => 'created',
            'table' => 'client',
            'row_id' => 1,
            'data' => '{"first_name":"Samuel","last_name":"Test","birthdate":null,"location":null,"phone":null,"email":"contact@samuel-gfeller.ch","sex":null,"client_message":null,"vigilance_level":null,"user_id":null,"client_status_id":1}',
            'datetime' => '2023-01-01 00:00:00',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/109.0',
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
