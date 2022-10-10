<?php


namespace App\Test\Provider\Client;


use App\Test\Fixture\FixtureTrait;
use App\Test\Fixture\UserFixture;
use Fig\Http\Message\StatusCodeInterface;

class ClientReadCaseProvider
{
    use FixtureTrait;

    /**
     * Provides where conditions for logged-in users and user that are linked to note
     * as well as the expected result
     *
     * @return array{
     *              array{
     *                  owner_user: array,
     *                  authenticated_user: array,
     *                  expected_result: array{
     *                                     creation: array,
     *                                     modification: array{main_note: array, normal_note: array},
     *                                     deletion: array{main_note: array, normal_note: array},
     *                                     }
     *                  }
     *           }
     */
    public function provideAuthenticatedAndLinkedUserForNote(): array
    {
        $userData = $this->findRecordsFromFixtureWhere(['role' => 'user'], UserFixture::class)[0];
        return [
            [ // ? Authenticated user is ressource owner - non admin
                // User to whom the note is linked
                'owner_user' => $userData,
                'authenticated_user' => $userData,
                'expected_result' => [
                    'creation' => [
                        StatusCodeInterface::class => StatusCodeInterface::STATUS_CREATED,
                    ],
                    'modification' => [
                        // All users may edit the main note but only change their own so there are different expected results
                        'main_note' => [
                            // For a PUT request: HTTP 200, HTTP 204 should imply "resource updated successfully"
                            // https://stackoverflow.com/a/2342589/9013718
                            StatusCodeInterface::class => StatusCodeInterface::STATUS_OK,
                            'json_response' => [
                                'status' => 'success',
                                'data' => null,
                            ],
                        ],
                        'normal_note' => [
                            StatusCodeInterface::class => StatusCodeInterface::STATUS_OK,
                            'json_response' => [
                                'status' => 'success',
                                'data' => null,
                            ],
                            'db_changed' => true,
                        ],
                    ],
                    'deletion' => [
                        // For a DELETE request: HTTP 200 or HTTP 204 should imply "resource deleted successfully"
                        StatusCodeInterface::class => StatusCodeInterface::STATUS_OK
                    ],
                ],
            ],
            [ // ? Authenticated user is admin - non ressource owner
                'owner_user' => $userData,
                'authenticated_user' => $this->findRecordsFromFixtureWhere(['role' => 'admin'], UserFixture::class)[0],
                'expected_result' => [
                    'creation' => [
                        StatusCodeInterface::class => StatusCodeInterface::STATUS_CREATED,
                    ],
                    'modification' => [
                        'main_note' => [
                            StatusCodeInterface::class => StatusCodeInterface::STATUS_OK,
                            'json_response' => [
                                'status' => 'success',
                                'data' => null,
                            ],
                        ],
                        'normal_note' => [
                            StatusCodeInterface::class => StatusCodeInterface::STATUS_OK,
                            'json_response' => [
                                'status' => 'success',
                                'data' => null,
                            ],
                            'db_changed' => true,
                        ],
                    ]
                ],
            ],
            [ // ? Authenticated user is not the ressource owner and not admin
                'owner_user' => $userData,
                // Get user with role user that is not the same then $userData
                'authenticated_user' => $this->findRecordsFromFixtureWhere(
                    ['role' => 'user'],
                    UserFixture::class,
                    ['id' => $userData['id']]
                )[0],
                'expected_result' => [
                    'creation' => [
                        // Should be created as users that are not linked to client are able to create notes - this will be different for mutation
                        StatusCodeInterface::class => StatusCodeInterface::STATUS_CREATED,
                    ],
                    'modification' => [
                        // All users may edit the main note but only change their own so there are different expected results
                        'main_note' => [
                            StatusCodeInterface::class => StatusCodeInterface::STATUS_OK,
                            'json_response' => [
                                'status' => 'success',
                                'data' => null,
                            ],
                        ],
                        'normal_note' => [
                            StatusCodeInterface::class => StatusCodeInterface::STATUS_FORBIDDEN,
                            'json_response' => [
                                'status' => 'error',
                                'message' => 'You can only edit your own note or need to be an admin to edit others'
                            ],
                            'db_changed' => false,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Returns combinations of invalid data to trigger validation exception
     * for note creation.
     *
     * @return array
     */
    public function provideInvalidNoteAndExpectedResponseDataForCreation(): array
    {
        // Message over 500 chars
        $tooLongMsg = 'iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii';

        return [
            [
                // Too short and already existing main note
                'request_body' => ['message' => 'Sh', 'is_main' => 1, 'client_id' => 1],
                'existing_main_note' => true,
                'json_response' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'message' => 'There is something in the note data that couldn\'t be validated',
                        'errors' => [
                            0 => [
                                'field' => 'message',
                                'message' => 'Required minimum length is 4',
                            ],
                            1 => [
                                'field' => 'is_main',
                                'message' => 'Main note exists already'
                            ],
                        ]
                    ]
                ]
            ],
            [
                // Too long
                'request_body' => ['message' => $tooLongMsg, 'is_main' => 1, 'client_id' => 1],
                'existing_main_note' => false,
                'json_response' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'message' => 'There is something in the note data that couldn\'t be validated',
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

    /**
     * Returns combinations of invalid data to trigger validation exception
     * for note modification.
     *
     * @return array
     */
    public function provideInvalidNoteAndExpectedResponseDataForModification(): array
    {
        // Message over 500 chars
        $tooLongMsg = 'iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii';

        return [
            [
                'message_too_short' => 'Me',
                'json_response' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'message' => 'There is something in the note data that couldn\'t be validated',
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
                'message_too_long' => $tooLongMsg,
                'json_response' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'message' => 'There is something in the note data that couldn\'t be validated',
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



    /**
     * Provide malformed note creation request body
     *
     * @return array
     */
    public function provideNoteCreationMalformedRequestBody(): array
    {
        return [
            [
                [
                    'message_wrong' => 'Message', // wrong message key name
                    'client_id' => 1,
                    'is_main' => 1,
                ],
            ],
            [
                [
                    'message' => 'Message',
                    'client_id_wrong' => 1, // wrong client id
                    'is_main' => 1,
                ],
            ],
            [
                [
                    'message' => 'Message',
                    'client_id' => 1,
                    'is_main_wrong' => 1, // wrong is_main
                ],
            ],
            [
                [ // One key too much
                    'message' => 'Message',
                    'client_id' => 1,
                    'is_main' => 1,
                    'extra_key' => 1, // wrong is_main
                ],
            ],
            [
                [ // Missing is_main
                    'message' => 'Message',
                    'client_id' => 1,
                ],
            ],

        ];
    }

    /**
     * Provide malformed note message request body
     *
     * @return array
     */
    public function provideMalformedNoteRequestBody(): array
    {
        return [
            [
                'wrong_key' => [
                    'wrong_message_key' => 'Message',
                ],
            ],
            [
                'wrong_amount' => [
                    'message' => 'Message',
                    'second_key' => 'invalid',
                ],
            ]
        ];
    }


}