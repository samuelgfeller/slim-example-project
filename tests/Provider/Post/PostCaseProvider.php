<?php


namespace App\Test\Provider\Post;


class PostCaseProvider
{
    /**
     * Returns combinations of malformed request bodies
     *
     * @return array
     */
    public function providePostCreateMalformedBody(): array
    {
        return [
            [['message_wrong' => 'Message name wrong.']],
            [['message' => 'This one is correct.', 'key2' => 'But with another key request body is wrong.']],
            [[]], // Empty request body is wrong as well
        ];
    }

    /**
     * Returns combinations of invalid data to trigger validation exception.
     *
     * @return array
     */
    public function providePostCreateInvalidData(): array
    {
        // Message over 500 chars
        $tooLongMsg = 'iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
            iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
            iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
            iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
            iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii';
        return [
            [
                'message_too_short' => ['message' => 'Me'],
                'validation_error_response' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'message' => 'There is something in the post data that couldn\'t be validated',
                        'errors' => [
                            0 => [
                                'field' => 'message',
                                'message' => 'Required minimum length is 4',
                            ]
                        ]
                    ]
                ]
            ],
            [
                'message_too_long' => ['message' => $tooLongMsg],
                'validation_error_response' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'message' => 'There is something in the post data that couldn\'t be validated',
                        'errors' => [
                            0 => [
                                'field' => 'message',
                                'message' => 'Required maximum length is 500',
                            ]
                        ]
                    ]
                ]
            ],

        ];
    }

}