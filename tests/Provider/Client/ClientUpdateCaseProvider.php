<?php

namespace App\Test\Provider\Client;

class ClientUpdateCaseProvider
{
    /**
     * Returns combinations of invalid data to trigger validation exception
     * for note creation.
     *
     * @return array
     */
    public function provideInvalidClientValuesAndExpectedResponseData(): array
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
                    'first_name' => 'iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
iiiiiiiiiiiiiiiiiiiiiiiiiiiii', // 101 chars
                    'last_name' => 'iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
iiiiiiiiiiiiiiiiiiiiiiiiiiiii', // 101 chars
                    'birthdate' => (new \DateTime())->modify('+1 day')->format('Y-m-d'), // 1 day in the future
                    'location' => 'iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
iiiiiiiiiiiiiiiiiiiiiiiiiiiii', // 101 chars
                    'phone' => '071 121 12 12 12', // 16 chars
                    'email' => 'test$@test.ch', // invalid email
                    'sex' => '', // empty string
                ],
                'json_response' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'message' => 'There is something in the client data that couldn\'t be validated',
                        'errors' => [
                            0 => [
                                'field' => 'first_name',
                                'message' => 'Required maximum length is 100',
                            ],
                            1 => [
                                'field' => 'last_name',
                                'message' => 'Required maximum length is 100',
                            ],
                            2 => [
                                'field' => 'birthdate',
                                'message' => 'Invalid birthdate',
                            ],
                            3 => [
                                'field' => 'location',
                                'message' => 'Required maximum length is 100',
                            ],
                            4 => [
                                'field' => 'phone',
                                'message' => 'Required maximum length is 15',
                            ],
                        ]
                    ]
                ]
            ]
        ];
    }
}
