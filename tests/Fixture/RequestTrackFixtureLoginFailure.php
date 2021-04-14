<?php

namespace App\Test\Fixture;

/**
 * This fixture inserts failed login requests exceeding the defined threshold
 * Exception should be thrown because:
 *  - Too many login requests on the same user
 *  - Too many login requests coming from the same IP
 * ! If threshold changes, success login entries should be added or removed accordingly
 */
class RequestTrackFixtureLoginFailure
{
    // Table name
    public string $table = 'request_track';

    // Database records in 2d array
    public array $records = [
        // 6 old successful login requests to make sure that request doesn't fail because the ratio of failures to
        // total requests is too low (global)
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
            'sent_email' => 0,
            'is_login' => 'success',
            'created_at' => '2021-01-01 00:00:01', // will be more than 24h ago
        ],
        [
            'id' => 3,
            'email' => 'user@example.com',
            'ip_address' => 2130706434, // 127.0.0.2 as unsigned int
            'sent_email' => 0,
            'is_login' => 'success',
            'created_at' => '2021-01-01 00:00:01', // will be more than 24h ago
        ],
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
        // 5 login requests all done within last second
//        [
//            'id' => 7,
//            'email' => 'toomanylogin.attempts@security.com',
//            'ip_address' => 2130706433, // 127.0.0.1 as unsigned int
//            'sent_email' => 0,
//            'is_login' => 'failure',
//            'created_at' => '2021-01-01 00:00:01', // will be in the present second
//        ],
//        [
//            'id' => 8,
//            'email' => 'toomanylogin.attempts@security.com',
//            'ip_address' => 2130706433, // 127.0.0.1 as unsigned int
//            'sent_email' => 0,
//            'is_login' => 'failure',
//            'created_at' => '2021-01-01 00:00:01', // will be in the present second
//        ],
//        [
//            'id' => 9,
//            'email' => 'toomanylogin.attempts@security.com',
//            'ip_address' => 2130706433, // 127.0.0.1 as unsigned int
//            'sent_email' => 0,
//            'is_login' => 'failure',
//            'created_at' => '2021-01-01 00:00:01', // will be in the present second
//        ],
//        [
//            'id' => 10,
//            'email' => 'toomanylogin.attempts@security.com',
//            'ip_address' => 2130706433, // 127.0.0.1 as unsigned int
//            'sent_email' => 0,
//            'is_login' => 'failure',
//            'created_at' => '2021-01-01 00:00:01', // will be in the present second
//        ],
//        [
//            'id' => 11,
//            'email' => 'toomanylogin.attempts@security.com',
//            'ip_address' => 2130706433, // 127.0.0.1 as unsigned int
//            'sent_email' => 0,
//            'is_login' => 'failure',
//            'created_at' => '2021-01-01 00:00:01', // will be in the present second
//        ],
//        // Other non failed request after 5 last failed requests to see if security check picks them up anyways
//        [
//            'id' => 12,
//            'email' => 'toomanylogin.attempts@security.com',
//            'ip_address' => 2130706433, // 127.0.0.1 as unsigned int
//            'sent_email' => 1,
//            'is_login' => null,
//            'created_at' => '2021-01-01 00:00:01', // will be in the present second
//        ],


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

        // Requests created 1sec ago
//        $recentDate = new \DateTime();
//        $recentDate->sub(new \DateInterval('PT01S')); // P - period, T - to indicate that next is time, S - seconds
//
//        for ($i = 7; $i <= 11; $i++) { // key 11 ist 12th array entry as it starts with 0
//            $this->records[$i]['created_at'] = $recentDate->format('Y-m-d H:i:s');
//        }
    }
}
