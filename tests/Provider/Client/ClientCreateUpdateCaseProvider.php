<?php

namespace App\Test\Provider\Client;

class ClientCreateUpdateCaseProvider
{
    /**
     * Returns combinations of invalid data to trigger validation exception
     * for client modification and creation.
     *
     * @return array
     */
    public function invalidClientValuesAndExpectedResponseData(): array
    {
        // The goal is to include as many values as possible that should trigger validation errors in each iteration
        return [
            [
                // Most values too short
                'request_body' => [
                    'first_name' => 'T',
                    'last_name' => 'A',
                    'birthdate' => '1850-01-01', // too old
                    'location' => 'La',
                    'phone' => '07',
                    'email' => 'test@test', // missing extension
                    'sex' => 'A', // invalid value
                    'user_id' => '999', // non-existing user
                    'client_status_id' => '999', // non-existing status
                ],
                'json_response' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'message' => 'There is something in the client data that couldn\'t be validated',
                        'errors' => [
                            0 => [
                                'field' => 'client_status',
                                'message' => 'Client_status not existing',
                            ],
                            1 => [
                                'field' => 'user',
                                'message' => 'User not existing',
                            ],
                            2 => [
                                'field' => 'first_name',
                                'message' => 'Required minimum length is 2',
                            ],
                            3 => [
                                'field' => 'last_name',
                                'message' => 'Required minimum length is 2',
                            ],
                            4 => [
                                'field' => 'email',
                                'message' => 'Invalid email address',
                            ],
                            5 => [
                                'field' => 'birthdate',
                                'message' => 'Invalid birthdate',
                            ],
                            6 => [
                                'field' => 'location',
                                'message' => 'Required minimum length is 3',
                            ],
                            7 => [
                                'field' => 'phone',
                                'message' => 'Required minimum length is 3',
                            ],
                            8 => [
                                'field' => 'sex',
                                'message' => 'Invalid sex value given. Allowed are M, F and O',
                            ],
                        ]
                    ]
                ]
            ],
            [
                // Most values too long
                'request_body' => [
                    'first_name' => str_repeat('i', 101), // 101 chars
                    'last_name' => str_repeat('i', 101),
                    'birthdate' => (new \DateTime())->modify('+1 day')->format('Y-m-d'), // 1 day in the future
                    'location' => str_repeat('i', 101),
                    'phone' => '+41 0071 121 12 12 12', // 21 chars
                    'email' => 'test$@test.ch', // invalid email
                    'sex' => '', // empty string
                    // All keys are needed as same dataset is used for create which always expects all keys
                    // and the json_response has to be equal too so the value can't be null.
                    'user_id' => '999', // non-existing user
                    'client_status_id' => '999', // non-existing status
                ],
                'json_response' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'message' => 'There is something in the client data that couldn\'t be validated',
                        'errors' => [
                            0 => [
                                'field' => 'client_status',
                                'message' => 'Client_status not existing',
                            ],
                            1 => [
                                'field' => 'user',
                                'message' => 'User not existing',
                            ],
                            2 => [
                                'field' => 'first_name',
                                'message' => 'Required maximum length is 100',
                            ],
                            3 => [
                                'field' => 'last_name',
                                'message' => 'Required maximum length is 100',
                            ],
                            4 => [
                                'field' => 'birthdate',
                                'message' => 'Invalid birthdate',
                            ],
                            5 => [
                                'field' => 'location',
                                'message' => 'Required maximum length is 100',
                            ],
                            6 => [
                                'field' => 'phone',
                                'message' => 'Required maximum length is 20',
                            ],
                        ]
                    ]
                ]
            ]
        ];
    }
}
