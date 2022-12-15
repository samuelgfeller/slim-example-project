<?php

namespace App\Test\Fixture;

/**
 * This fixture inserts valid requests.
 */
class UserRequestFixture
{
    // Table name
    public string $table = 'user_request';

    // Database records in 2d array
    public array $records = [
        [
            'id' => 1,
            'email' => 'admin@example.com',
            'ip_address' => 2130706433, // 127.0.0.1 as unsigned int
            'sent_email' => 0,
            'is_login' => 'success',
            'created_at' => '2021-01-01 00:00:01', // will be more than 24h ago
        ],
        [
            'id' => 2,
            'email' => 'admin@example.com',
            'ip_address' => 2130706433, // 127.0.0.1 as unsigned int
            'sent_email' => 1,
            'is_login' => null,
            'created_at' => '2021-01-01 00:00:01', // will be more than 24h ago
        ],
        [
            'id' => 3,
            'email' => 'user@example.com',
            'ip_address' => 2130706434, // 127.0.0.2 as unsigned int
            'sent_email' => 1,
            'is_login' => null,
            'created_at' => '2021-01-01 00:00:01', // will be more than 24h ago
        ],
        // More normal requests
        [
            'id' => 4,
            'email' => 'user@example.com',
            'ip_address' => 2130706434, // 127.0.0.2 as unsigned int
            'sent_email' => 0,
            'is_login' => 'success',
            'created_at' => '2021-01-01 00:00:01', // will be less than 1h ago
        ],
        [
            'id' => 5,
            'email' => 'admin@example.com',
            'ip_address' => 2130706433, // 127.0.0.1 as unsigned int
            'sent_email' => 0,
            'is_login' => 'success',
            'created_at' => '2021-01-01 00:00:01', // will be less than 1h ago
        ],
        [
            'id' => 6,
            'email' => 'admin@example.com',
            'ip_address' => 2130706433, // 127.0.0.1 as unsigned int
            'sent_email' => 1,
            'is_login' => null,
            'created_at' => '2021-01-01 00:00:01', // will be less than 1h ago
        ],
    ];

    public function __construct()
    {
        // Requests created 25h ago
        $oldDate = new \DateTime();
        $oldDate->sub(new \DateInterval('PT26H'));

        $this->records[0]['created_at'] = $oldDate->format('Y-m-d H:i:s');
        $this->records[1]['created_at'] = $oldDate->format('Y-m-d H:i:s');
        $this->records[2]['created_at'] = $oldDate->format('Y-m-d H:i:s');

        // Requests created 30min ago
        $recentDate = new \DateTime();
        $recentDate->sub(new \DateInterval('PT30M')); // P - period, T - to indicate that next is time, M - minutes

        $this->records[3]['created_at'] = $recentDate->format('Y-m-d H:i:s');
        $this->records[4]['created_at'] = $recentDate->format('Y-m-d H:i:s');
        $this->records[5]['created_at'] = $recentDate->format('Y-m-d H:i:s');
    }
}
