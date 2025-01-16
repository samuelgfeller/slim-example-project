<?php

namespace App\Test\TestCase\Note\Create;

class NoteCreateProvider
{

    /**
     * Returns combinations of invalid data to trigger validation exception
     * for note creation.
     *
     * @return array
     */
    public static function invalidNoteCreationProvider(): array
    {
        return [
            [
                // Already existing main note (message as string shorter than 4 chars is allowed)
                'invalidRequestBody' => ['message' => 'as', 'is_main' => 1, 'client_id' => 1],
                'existingMainNote' => true,
                'expectedResponseData' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'errors' => [
                            'is_main' => [
                                0 => 'Main note already exists',
                            ],
                        ],
                    ],
                ],
            ],
            [
                // Note message too short
                'invalidRequestBody' => ['message' => 'as', 'is_main' => 0, 'client_id' => 1],
                'existingMainNote' => true,
                'expectedResponseData' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'errors' => [
                            'message' => [
                                0 => 'Minimum length is 4',
                            ],
                        ],
                    ],
                ],
            ],
            [
                // Too long
                'invalidRequestBody' => ['message' => str_repeat('i', 1001), 'is_main' => 0, 'client_id' => 1],
                'existingMainNote' => false,
                'expectedResponseData' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'errors' => [
                            'message' => [
                                0 => 'Maximum length is 1000',
                            ],
                        ],
                    ],
                ],
            ],
            [
                // Check keys in request body (previously done via malformedBodyRequestChecker)
                'invalidRequestBody' => [],
                'existingMainNote' => false,
                'expectedResponseData' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'errors' => [
                            'message' => [
                                0 => 'Field is required',
                            ],
                            'is_main' => [
                                0 => 'Field is required',
                            ],
                            'client_id' => [
                                0 => 'Field is required',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
