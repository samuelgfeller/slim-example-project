<?php


namespace App\Test\Provider\Note;


use App\Domain\Authorization\Privilege;
use App\Test\Fixture\FixtureTrait;
use App\Test\Fixture\UserFixture;
use Fig\Http\Message\StatusCodeInterface;

class NoteCaseProvider
{
    use FixtureTrait;

    /**
     * Even though this is for the list test, one note
     * at a time is tested for clarity and simplicity.
     *
     * @return array[]
     */
    public function provideUsersNotesAndExpectedResultForList(): array
    {
        // Get users with the different roles
        $managingAdvisorData = $this->getFixtureRecordsWithAttributes(['user_role_id' => 2], UserFixture::class);
        $advisorData = $this->getFixtureRecordsWithAttributes(['user_role_id' => 3], UserFixture::class);
        $newcomerData = $this->getFixtureRecordsWithAttributes(['user_role_id' => 4], UserFixture::class);

        return [
            [// ? newcomer not owner of note
                'note_owner' => $advisorData,
                'authenticated_user' => $newcomerData,
                'expected_result' => [
                    StatusCodeInterface::class => StatusCodeInterface::STATUS_OK,
                    'privilege' => Privilege::CREATE
                ],
            ],
            [// ? newcomer owner of note
                'note_owner' => $newcomerData,
                'authenticated_user' => $newcomerData,
                'expected_result' => [
                    StatusCodeInterface::class => StatusCodeInterface::STATUS_OK,
                    'privilege' => Privilege::DELETE
                ],
            ],
            // Advisor would be the same as newcomer
            [// ? managing advisor not owner of note
                'note_owner' => $advisorData,
                'authenticated_user' => $managingAdvisorData,
                'expected_result' => [
                    StatusCodeInterface::class => StatusCodeInterface::STATUS_OK,
                    // Full privilege, so it must not be tested further
                    'privilege' => Privilege::DELETE
                ],
            ],
        ];
    }

    /**
     * Provides logged-in user and user linked to note along the expected result
     * As the permissions are a lot more simple than for client for instance,
     * CRUD cases are all in this function
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
    public function provideUsersAndExpectedResultForNoteCrud(): array
    {
        // Get users with the different roles
        $managingAdvisorData = $this->getFixtureRecordsWithAttributes(['user_role_id' => 2], UserFixture::class);
        $advisorData = $this->getFixtureRecordsWithAttributes(['user_role_id' => 3], UserFixture::class);
        $newcomerData = $this->getFixtureRecordsWithAttributes(['user_role_id' => 4], UserFixture::class);

        $authorizedResult = [
            // For a DELETE, PUT request: HTTP 200, HTTP 204 should imply "resource updated successfully"
            // https://stackoverflow.com/a/2342589/9013718
            StatusCodeInterface::class => StatusCodeInterface::STATUS_OK,
            // Is db supposed to change
            'db_changed' => true,
            'json_response' => [
                'status' => 'success',
                'data' => null,
            ],
        ];
        $unauthorizedResult = [
            StatusCodeInterface::class => StatusCodeInterface::STATUS_FORBIDDEN,
            'db_changed' => false,
            'json_response' => [
                'status' => 'error',
                'message' => 'Not allowed to change note.',
            ]
        ];
        $unauthorizedUpdateResult = $unauthorizedResult;
        $unauthorizedUpdateResult['json_response']['message'] = 'Not allowed to change note.';
        $unauthorizedDeleteResult = $unauthorizedResult;
        $unauthorizedDeleteResult['json_response']['message'] = 'Not allowed to delete note.';
        $authorizedCreateResult = [StatusCodeInterface::class => StatusCodeInterface::STATUS_CREATED];

        return [
            [ // ? newcomer not owner
                // User to whom the note (or client for creation) is linked
                'owner_user' => $advisorData,
                'authenticated_user' => $newcomerData,
                'expected_result' => [
                    // Allowed to create note on client where user is not owner
                    'creation' => $authorizedCreateResult,
                    'modification' => [
                        'main_note' => $unauthorizedUpdateResult,
                        'normal_note' => $unauthorizedResult,
                    ],
                    'deletion' => [
                        // Delete main note request is expected to produce 405 HttpMethodNotAllowedException
                        'normal_note' => $unauthorizedDeleteResult
                    ],
                ],
            ],
            [ // ? newcomer owner
                // User to whom the note (or client for creation) is linked
                'owner_user' => $newcomerData,
                'authenticated_user' => $newcomerData,
                'expected_result' => [
                    'creation' => [
                        StatusCodeInterface::class => StatusCodeInterface::STATUS_CREATED,
                    ],
                    'modification' => [
                        // Newcomer may not edit client basic data which has the same rights as the main note
                        'main_note' => $unauthorizedUpdateResult,
                        'normal_note' => $authorizedResult,
                    ],
                    'deletion' => [
                        // Delete main note request is expected to produce 405 HttpMethodNotAllowedException
                        'normal_note' => $authorizedResult
                    ],
                ],
            ],
            [ // ? advisor owner
                // User to whom the note (or client for creation) is linked
                'owner_user' => $advisorData,
                'authenticated_user' => $advisorData,
                'expected_result' => [
                    'creation' => [ // Allowed to create note on client where user is not owner
                        StatusCodeInterface::class => StatusCodeInterface::STATUS_CREATED,
                    ],
                    'modification' => [
                        'main_note' => $authorizedResult,
                        'normal_note' => $authorizedResult,
                    ],
                    'deletion' => [
                        // Delete main note request is expected to produce 405 HttpMethodNotAllowedException
                        'normal_note' => $authorizedResult
                    ],
                ],
            ],
            [ // ? advisor not owner
                // User to whom the note (or client for creation) is linked
                'owner_user' => $managingAdvisorData,
                'authenticated_user' => $advisorData,
                'expected_result' => [
                    'creation' => $authorizedCreateResult,
                    'modification' => [
                        'main_note' => $authorizedResult,
                        'normal_note' => $unauthorizedUpdateResult,
                    ],
                    'deletion' => [
                        // Delete main note request is expected to produce 405 HttpMethodNotAllowedException
                        'normal_note' => $unauthorizedDeleteResult
                    ],
                ],
            ],
            [ // ? managing advisor not owner
                // User to whom the note (or client for creation) is linked
                'owner_user' => $advisorData,
                'authenticated_user' => $managingAdvisorData,
                'expected_result' => [
                    'creation' => $authorizedCreateResult,
                    'modification' => [
                        'main_note' => $authorizedResult,
                        'normal_note' => $authorizedResult,
                    ],
                    'deletion' => [
                        // Delete main note request is expected to produce 405 HttpMethodNotAllowedException
                        'normal_note' => $authorizedResult
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
                'request_body' => ['message' => $tooLongMsg, 'is_main' => 0, 'client_id' => 1],
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