<?php

namespace App\Test\Provider\Client;

class ApiClientCreateProvider
{
    /**
     * Returns combinations of invalid data to trigger validation exception
     * for client creation from public page.
     *
     * @return array
     */
    public function invalidApiClientCreationValues(): array
    {
        // Including as many values as possible that trigger validation errors in each case
        return [
            [
                // Most values too short
                'request_body' => [
                    'first_name' => 'T',
                    'last_name' => 'A',
                    'birthdate' => '1850-01-01', // too old
                    'location' => 'L',
                    'phone' => '07',
                    'email' => 'test@test', // missing extension
                    'sex' => 'A', // invalid value
                    'client_message' => 'A',
                ],
                'json_response' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'message' => 'There is something in the client data that couldn\'t be validated',
                        'errors' => [
                            0 => [
                                'field' => 'first_name',
                                'message' => 'Minimum length is 2',
                            ],
                            1 => [
                                'field' => 'last_name',
                                'message' => 'Minimum length is 2',
                            ],
                            2 => [
                                'field' => 'email',
                                'message' => 'Invalid email address',
                            ],
                            3 => [
                                'field' => 'birthdate',
                                'message' => 'Invalid birthdate',
                            ],
                            4 => [
                                'field' => 'location',
                                'message' => 'Minimum length is 2',
                            ],
                            5 => [
                                'field' => 'phone',
                                'message' => 'Minimum length is 3',
                            ],
                            6 => [
                                'field' => 'sex',
                                'message' => 'Invalid sex value given.',
                            ],
                            7 => [
                                'field' => 'client_message',
                                'message' => 'Minimum length is 3',
                            ],
                        ],
                    ],
                ],
            ],
            [
                // Most values too long
                'request_body' => [
                    'first_name' => str_repeat('i', 101), // 101 chars
                    'last_name' => str_repeat('i', 101),
                    'birthdate' => (new \DateTime())->modify('+1 day')->format('Y-m-d'), // 1 day in the future
                    'location' => str_repeat('i', 101),
                    'phone' => '+41 0071 121 12 12 12', // 21 chars
                    'email' => 'test$.@test.ch', // invalid email
                    'client_message' => str_repeat('i', 1001),
                ],
                'json_response' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'message' => 'There is something in the client data that couldn\'t be validated',
                        'errors' => [
                            0 => [
                                'field' => 'first_name',
                                'message' => 'Maximum length is 100',
                            ],
                            1 => [
                                'field' => 'last_name',
                                'message' => 'Maximum length is 100',
                            ],
                            2 => [
                                'field' => 'email',
                                'message' => 'Invalid email address',
                            ],
                            3 => [
                                'field' => 'birthdate',
                                'message' => 'Invalid birthdate',
                            ],
                            4 => [
                                'field' => 'location',
                                'message' => 'Maximum length is 100',
                            ],
                            5 => [
                                'field' => 'phone',
                                'message' => 'Maximum length is 20',
                            ],
                            6 => [
                                'field' => 'client_message',
                                'message' => 'Maximum length is 1000',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Provide malformed request body for client creation.
     *
     * @return array[]
     */
    public function malformedRequestBodyCases(): array
    {
        return [
            [
                // If any of the list except client_message is missing it's a bad request
                'missing_first_name' => [
                    'last_name' => 'value',
                    'birthdate' => 'value',
                    'location' => 'value',
                    'phone' => 'value',
                    'email' => 'value',
                    'sex' => 'value',
                    'client_message' => 'value',
                ],
                'key_too_much_without_client_message' => [
                    'first_name' => 'value',
                    'last_name' => 'value',
                    'birthdate' => 'value',
                    'location' => 'value',
                    'phone' => 'value',
                    'email' => 'value',
                    'sex' => 'value',
                    // 'client_message' => 'value',
                    'key_too_much' => 'value',
                ],
            ],
        ];
    }
}
