<?php

namespace App\Test\Fixture;

/**
 * Client values that can be inserted into the database.
 */
class ClientFixture
{
    // Table name
    public string $table = 'client';

    // Database records in 2d array
    public array $records = [
        // Foreign keys should always have default value 1
        [
            'id' => 1,
            'first_name' => 'Rachel',
            'last_name' => 'Harmon',
            'birthdate' => '1980-06-21',
            'location' => 'Basel',
            'phone' => '079 364 33 28',
            'email' => 'rachel.harmon@email.com',
            'sex' => 'F',
            'client_message' => null,
            'user_id' => 1,
            'client_status_id' => 1,
            'updated_at' => '2021-01-01 00:00:01',
            'created_at' => '2022-01-01 00:00:01',
            'deleted_at' => null,
        ],
        [
            'id' => 2,
            'first_name' => 'Timon Koch',
            'last_name' => 'Harmon',
            'birthdate' => '1972-02-19',
            'location' => 'Bern',
            'phone' => '077 878 24 99',
            'email' => 'timon.koch@email.com',
            'sex' => 'M',
            'client_message' => null,
            'user_id' => 1,
            'client_status_id' => 1,
            'updated_at' => '2021-01-01 00:00:01',
            'created_at' => '2022-01-01 00:00:01',
            'deleted_at' => null,
        ],
        [
            'id' => 3,
            'first_name' => 'Silvia',
            'last_name' => 'Perez',
            'birthdate' => '1985-08-04',
            'location' => 'Basel',
            'phone' => '076 662 73 48',
            'email' => 'rachel.harmon@email.com',
            'sex' => 'F',
            'client_message' => 'Client message submitted by Silvia Perez',
            'user_id' => 1,
            'client_status_id' => 1,
            'updated_at' => '2021-01-01 00:00:01',
            'created_at' => '2022-01-01 00:00:01',
            'deleted_at' => null,
        ],
        [
            'id' => 4,
            'first_name' => 'Deleted',
            'last_name' => 'Client',
            'birthdate' => '1955-03-20',
            'location' => 'Basel',
            'phone' => '076 877 33 52',
            'email' => 'deleted.client@email.com',
            'sex' => 'M',
            'client_message' => 'Client message submitted by Deleted Client',
            'user_id' => 1,
            'client_status_id' => 1,
            'updated_at' => '2021-01-01 00:00:01',
            'created_at' => '2022-01-01 00:00:01',
            'deleted_at' => '2022-01-01 00:00:02',
        ],
        // Client id 5: user linked is admin, status id 1
        [
            'id' => 5,
            'first_name' => 'Client to',
            'last_name' => 'Admin',
            'birthdate' => '1980-06-21',
            'location' => 'Basel',
            'phone' => '079 364 33 28',
            'email' => 'client.to.admin@email.com',
            'sex' => 'F',
            'client_message' => 'Test client message',
            'user_id' => 1,
            'client_status_id' => 1,
            'updated_at' => '2021-01-01 00:00:01',
            'created_at' => '2022-01-01 00:00:01',
            'deleted_at' => null,
        ],
    ];
}
