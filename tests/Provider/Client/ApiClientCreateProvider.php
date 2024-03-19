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
    public static function invalidApiClientCreationValues(): array
    {
        // Including as many values as possible that trigger validation errors in each case
        return [
            [
                // Most values too short
                'requestBody' => [
                    'first_name' => 'T',
                    'last_name' => 'A',
                    'birthdate' => '1850-01-01', // too old
                    'location' => 'L',
                    'phone' => '07',
                    'email' => 'test@test', // missing extension
                    'sex' => 'A', // invalid value
                    'client_message' => 'A',
                ],
                'jsonResponse' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'errors' => [
                            'first_name' => [
                                0 => 'Minimum length is 2',
                            ],
                            'last_name' => [
                                0 => 'Minimum length is 2',
                            ],
                            'email' => [
                                0 => 'Invalid email',
                            ],
                            'birthdate' => [
                                0 => 'Cannot be older than 130 years',
                            ],
                            'location' => [
                                0 => 'Minimum length is 2',
                            ],
                            'phone' => [
                                0 => 'Minimum length is 3',
                            ],
                            'sex' => [
                                0 => 'Invalid option',
                            ],
                            'client_message' => [
                                0 => 'Minimum length is 3',
                            ],
                        ],
                    ],
                ],
            ],
            [
                // Most values too long
                'requestBody' => [
                    'first_name' => str_repeat('i', 101), // 101 chars
                    'last_name' => str_repeat('i', 101),
                    'birthdate' => (new \DateTime())->modify('+1 day')->format('Y-m-d'), // 1 day in the future
                    'location' => str_repeat('i', 101),
                    'phone' => '+41 0071 121 12 12 12', // 21 chars
                    'email' => 'test$.@test.ch', // invalid email
                    'client_message' => str_repeat('i', 1001),
                ],
                'jsonResponse' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'errors' => [
                            'first_name' => [
                                0 => 'Maximum length is 100',
                            ],
                            'last_name' => [
                                0 => 'Maximum length is 100',
                            ],
                            'email' => [
                                0 => 'Invalid email',
                            ],
                            'birthdate' => [
                                0 => 'Cannot be in the future',
                            ],
                            'location' => [
                                0 => 'Maximum length is 100',
                            ],
                            'phone' => [
                                0 => 'Maximum length is 20',
                            ],
                            'client_message' => [
                                0 => 'Maximum length is 1000',
                            ],
                        ],
                    ],
                ],
            ],
            [ // Keys missing, check for request body key presence (previously done via malformedBodyRequestChecker)
                // Empty request body
                'requestBody' => [
                ],
                'jsonResponse' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'errors' => [
                            'first_name' => [
                                0 => 'Field is required',
                            ],
                            'last_name' => [
                                0 => 'Field is required',
                            ],
                            'email' => [
                                0 => 'Field is required',
                            ],
                            'birthdate' => [
                                0 => 'Field is required',
                            ],
                            'location' => [
                                0 => 'Field is required',
                            ],
                            'phone' => [
                                0 => 'Field is required',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
