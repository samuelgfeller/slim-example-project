<?php

namespace App\Test\TestCase\Note\Update;

class NoteUpdateProvider
{
    /**
     * Returns combinations of invalid data to trigger validation exception
     * for note modification.
     *
     * @return array
     */
    public static function invalidNoteUpdateProvider(): array
    {
        return [
            [
                // Message too short
                'invalidRequestBody' => ['message' => 'M'],
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
                // Message too long
                'invalidRequestBody' => ['message' => str_repeat('i', 1001)],
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
                // Missing message key
                'invalidRequestBody' => [],
                'expectedResponseData' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'errors' => [
                            'is_main' => [
                                0 => 'Request body is empty',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
