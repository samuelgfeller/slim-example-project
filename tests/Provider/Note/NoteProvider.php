<?php

namespace App\Test\Provider\Note;

use App\Domain\Authorization\Privilege;
use App\Domain\User\Enum\UserRole;
use App\Test\Traits\FixtureTestTrait;
use Fig\Http\Message\StatusCodeInterface;

class NoteProvider
{
    use FixtureTestTrait;

    /**
     * One note at a time is tested for clarity and simplicity.
     *
     * @return array[]
     */
    public function noteListUserAttributesAndExpectedResultProvider(): array
    {
        // Set different user role attributes
        $managingAdvisorRow = ['user_role_id' => UserRole::MANAGING_ADVISOR];
        $advisorRow = ['user_role_id' => UserRole::ADVISOR];
        $newcomerRow = ['user_role_id' => UserRole::NEWCOMER];

        return [
            [// ? newcomer not owner of note - note NOT hidden - allowed to read
                'note_owner' => $advisorRow,
                'authenticated_user' => $newcomerRow,
                'note_hidden' => null,
                'expected_result' => [
                    'privilege' => Privilege::READ,
                ],
            ],
            [// ? newcomer not owner of note - note hidden - not allowed to read
                'note_owner' => $advisorRow,
                'authenticated_user' => $newcomerRow,
                'note_hidden' => 1,
                'expected_result' => [
                    'privilege' => Privilege::NONE,
                ],
            ],
            [// ? newcomer owner of note - note hidden - allowed to delete
                'note_owner' => $newcomerRow,
                'authenticated_user' => $newcomerRow,
                'note_hidden' => 1,
                'expected_result' => [
                    'privilege' => Privilege::DELETE,
                ],
            ],
            [// ? advisor not owner of note - note hidden - allowed to read
                'note_owner' => $managingAdvisorRow,
                'authenticated_user' => $advisorRow,
                'note_hidden' => 1,
                'expected_result' => [
                    'privilege' => Privilege::READ,
                ],
            ],
            [// ? managing advisor not owner of note - note hidden - allowed to do everything
                'note_owner' => $advisorRow,
                'authenticated_user' => $managingAdvisorRow,
                'note_hidden' => 1,
                'expected_result' => [
                    // Full privilege, so it must not be tested further
                    'privilege' => Privilege::DELETE,
                ],
            ],
        ];
    }

    /**
     * Provides logged-in user and user linked to note along the expected result
     * As the permissions are a lot more simple than for client for instance,
     * Create Update Delete cases are all in this function.
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
    public function noteCUDUserAttributesAndExpectedResultProvider(): array
    {
        // Set different user role attributes
        $managingAdvisorAttributes = ['user_role_id' => UserRole::MANAGING_ADVISOR];
        $advisorAttributes = ['user_role_id' => UserRole::ADVISOR];
        $newcomerAttributes = ['user_role_id' => UserRole::NEWCOMER];

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
            ],
        ];
        $unauthorizedUpdateResult = $unauthorizedResult;
        $unauthorizedUpdateResult['json_response']['message'] = 'Not allowed to change note.';
        $unauthorizedDeleteResult = $unauthorizedResult;
        $unauthorizedDeleteResult['json_response']['message'] = 'Not allowed to delete note.';
        $authorizedCreateResult = [StatusCodeInterface::class => StatusCodeInterface::STATUS_CREATED];

        return [
            [ // ? newcomer not owner
                // User to whom the note (or client for creation) is linked
                'owner_user' => $advisorAttributes,
                'authenticated_user' => $newcomerAttributes,
                'expected_result' => [
                    // Allowed to create note on client where user is not owner
                    'creation' => $authorizedCreateResult,
                    'modification' => [
                        'main_note' => $unauthorizedUpdateResult,
                        'normal_note' => $unauthorizedResult,
                    ],
                    'deletion' => [
                        // Delete main note request is expected to produce 405 HttpMethodNotAllowedException
                        'normal_note' => $unauthorizedDeleteResult,
                    ],
                ],
            ],
            [ // ? newcomer owner
                // User to whom the note (or client for creation) is linked
                'owner_user' => $newcomerAttributes,
                'authenticated_user' => $newcomerAttributes,
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
                        'normal_note' => $authorizedResult,
                    ],
                ],
            ],
            [ // ? advisor owner
                // User to whom the note (or client for creation) is linked
                'owner_user' => $advisorAttributes,
                'authenticated_user' => $advisorAttributes,
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
                        'normal_note' => $authorizedResult,
                    ],
                ],
            ],
            [ // ? advisor not owner
                // User to whom the note (or client for creation) is linked
                'owner_user' => $managingAdvisorAttributes,
                'authenticated_user' => $advisorAttributes,
                'expected_result' => [
                    'creation' => $authorizedCreateResult,
                    'modification' => [
                        'main_note' => $authorizedResult,
                        'normal_note' => $unauthorizedUpdateResult,
                    ],
                    'deletion' => [
                        // Delete main note request is expected to produce 405 HttpMethodNotAllowedException
                        'normal_note' => $unauthorizedDeleteResult,
                    ],
                ],
            ],
            [ // ? managing advisor not owner
                // User to whom the note (or client for creation) is linked
                'owner_user' => $advisorAttributes,
                'authenticated_user' => $managingAdvisorAttributes,
                'expected_result' => [
                    'creation' => $authorizedCreateResult,
                    'modification' => [
                        'main_note' => $authorizedResult,
                        'normal_note' => $authorizedResult,
                    ],
                    'deletion' => [
                        // Delete main note request is expected to produce 405 HttpMethodNotAllowedException
                        'normal_note' => $authorizedResult,
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
    public function clientCreationInvalidNoteAndExpectedResponseProvider(): array
    {
        return [
            [
                // Already existing main note (message as string shorter than 4 chars is allowed)
                'request_body' => ['message' => 'as', 'is_main' => 1, 'client_id' => 1],
                'existing_main_note' => true,
                'json_response' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'message' => 'There is something in the note data that couldn\'t be validated',
                        'errors' => [
                            0 => [
                                'field' => 'is_main',
                                'message' => 'Main note exists already',
                            ],
                        ],
                    ],
                ],
            ],
            [
                // Note message too short
                'request_body' => ['message' => 'as', 'is_main' => 0, 'client_id' => 1],
                'existing_main_note' => true,
                'json_response' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'message' => 'There is something in the note data that couldn\'t be validated',
                        'errors' => [
                            0 => [
                                'field' => 'message',
                                'message' => 'Minimum length is 4',
                            ],
                        ],
                    ],
                ],
            ],
            [
                // Too long
                'request_body' => ['message' => str_repeat('i', 1001), 'is_main' => 0, 'client_id' => 1],
                'existing_main_note' => false,
                'json_response' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'message' => 'There is something in the note data that couldn\'t be validated',
                        'errors' => [
                            0 => [
                                'field' => 'message',
                                'message' => 'Maximum length is 1000',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Returns combinations of invalid data to trigger validation exception
     * for note modification.
     *
     * @return array
     */
    public function provideInvalidNoteAndExpectedResponseDataForUpdate(): array
    {
        return [
            [
                'message_too_short' => 'M',
                'json_response' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'message' => 'There is something in the note data that couldn\'t be validated',
                        'errors' => [
                            0 => [
                                'field' => 'message',
                                'message' => 'Minimum length is 4',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'message_too_long' => str_repeat('i', 1001),
                'json_response' => [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'data' => [
                        'message' => 'There is something in the note data that couldn\'t be validated',
                        'errors' => [
                            0 => [
                                'field' => 'message',
                                'message' => 'Maximum length is 1000',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Provide malformed note creation request body.
     *
     * @return array
     */
    public function provideNoteMalformedRequestBodyForCreation(): array
    {
        return [
            [
                // Empty body
                'requestBody' => [],
            ],
            [
                // Body "null" (because both can happen )
                'requestBody' => null,
            ],
            [
                'requestBody' => [
                    'message_wrong' => 'Message', // wrong message key name
                    'client_id' => 1,
                    'is_main' => 1,
                ],
            ],
            [
                'requestBody' => [
                    'message' => 'Message',
                    'client_id_wrong' => 1, // wrong client id
                    'is_main' => 1,
                ],
            ],
            [
                'requestBody' => [
                    'message' => 'Message',
                    'client_id' => 1,
                    'is_main_wrong' => 1, // wrong is_main
                ],
            ],
            [
                'requestBody' => [ // One key too much
                    'message' => 'Message',
                    'client_id' => 1,
                    'is_main' => 1,
                    'extra_key' => 1, // wrong is_main
                ],
            ],
            [
                'requestBody' => [ // Missing is_main
                    'message' => 'Message',
                    'client_id' => 1,
                ],
            ],
        ];
    }

    /**
     * Provide malformed note message request body.
     *
     * @return array
     */
    public function provideMalformedNoteRequestBodyForUpdate(): array
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
            ],
        ];
    }
}
