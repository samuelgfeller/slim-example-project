<?php

namespace App\Test\Provider\Note;

use App\Domain\Authorization\Privilege;
use App\Domain\User\Enum\UserRole;
use Fig\Http\Message\StatusCodeInterface;

class NoteProvider
{
    /**
     * One note at a time is tested for clarity and simplicity.
     *
     * @return array[]
     */
    public static function noteListUserAttributesAndExpectedResultProvider(): array
    {
        // Set different user role attributes
        $managingAdvisorRow = ['user_role_id' => UserRole::MANAGING_ADVISOR];
        $advisorRow = ['user_role_id' => UserRole::ADVISOR];
        $newcomerRow = ['user_role_id' => UserRole::NEWCOMER];

        return [
            [// ? newcomer not owner of note - note NOT hidden - allowed to read
                'userLinkedToNoteRow' => $advisorRow,
                'authenticatedUserRow' => $newcomerRow,
                'noteHidden' => null,
                'expectedResult' => [
                    'privilege' => Privilege::R,
                ],
            ],
            [// ? newcomer not owner of note - note hidden - not allowed to read
                'userLinkedToNoteRow' => $advisorRow,
                'authenticatedUserRow' => $newcomerRow,
                'noteHidden' => 1,
                'expectedResult' => [
                    'privilege' => Privilege::N,
                ],
            ],
            [// ? newcomer owner of note - note hidden - allowed to delete
                'userLinkedToNoteRow' => $newcomerRow,
                'authenticatedUserRow' => $newcomerRow,
                'noteHidden' => 1,
                'expectedResult' => [
                    'privilege' => Privilege::CRUD,
                ],
            ],
            [// ? advisor not owner of note - note hidden - allowed to read
                'userLinkedToNoteRow' => $managingAdvisorRow,
                'authenticatedUserRow' => $advisorRow,
                'noteHidden' => 1,
                'expectedResult' => [
                    'privilege' => Privilege::R,
                ],
            ],
            [// ? managing advisor not owner of note - note hidden - allowed to do everything
                'userLinkedToNoteRow' => $advisorRow,
                'authenticatedUserRow' => $managingAdvisorRow,
                'noteHidden' => 1,
                'expectedResult' => [
                    // Full privilege, so it must not be tested further
                    'privilege' => Privilege::CRUD,
                ],
            ],
        ];
    }

    /**
     * Note filter test provider.
     *
     * @return array[]
     */
    public static function noteListWithFilterProvider(): array
    {
        // Users linked to notes to insert (authenticated user not relevant for this test, inserted in test case)
        $usersToInsert = [
            ['id' => 10],
            ['id' => 11],
            ['id' => 12],
        ];

        $sqlDateTime = (new \DateTime())->format('Y-m-d H:i:s');

        // Client to insert (client status is inserted automatically in test case)
        $clientToInsert = ['id' => 1, 'first_name' => 'Max'];

        // Notes to insert
        $notesToInsert = [
            // Belong to user 11 and client 1
            ['user_id' => 11, 'client_id' => 1, 'is_main' => 0, 'message' => 'belongs to user 11 and client 1'],
            ['user_id' => 11, 'client_id' => 1, 'is_main' => 0, 'message' => 'also user 11 and client 1'],
            // Main note
            ['user_id' => 11, 'client_id' => 1, 'is_main' => 1, 'message' => 'main note user 11 and client 1'],
            [// Belongs to user 11 and client 1 but is deleted
                'user_id' => 11,
                'client_id' => 1,
                'is_main' => 0,
                'deleted_at' => $sqlDateTime,
                'message' => 'deleted c1 u11',
            ],
            // Belongs to user 12 and client 1
            ['user_id' => 12, 'client_id' => 1, 'is_main' => 0, 'message' => 'client 1 user 12'],
        ];

        return [
            // * Filter "client_id"
            [ // client 1 (not tested with other clients)
                'filterQueryParams' => ['client_id' => '1'],
                // Expected where string to search in the note table
                'expectedNotesWhereString' => 'deleted_at IS NULL AND is_main = 0 AND client_id = 1',
                'usersAttrToInsert' => $usersToInsert,
                'clientAttrToInsert' => $clientToInsert,
                'notesAttrToInsert' => $notesToInsert,
            ],
            // * Filter "most-recent"
            [ // most-recent value is the amount recent notes that should be returned
                'filterQueryParams' => ['most-recent' => 3],
                // Expected where string to search in the note table (order and desc added in the where string
                // may break and has to be adapted if DatabaseExtensionTestTrait->findTableRowsWhere() changes)
                'expectedNotesWhereString' => 'deleted_at IS NULL AND is_main = 0 ORDER BY updated_at DESC LIMIT 3',
                'usersAttrToInsert' => $usersToInsert,
                'clientAttrToInsert' => $clientToInsert,
                'notesAttrToInsert' => $notesToInsert,
            ],
            // * Filter "user_id"
            [ // user 2
                'filterQueryParams' => ['user' => 11],
                // Expected where string to search in the note table
                'expectedNotesWhereString' => 'deleted_at IS NULL AND is_main = 0 AND user_id = 11',
                'usersAttrToInsert' => $usersToInsert,
                'clientAttrToInsert' => $clientToInsert,
                'notesAttrToInsert' => $notesToInsert,
            ],
        ];
    }

    /**
     * Note list filters require the value to be in a specific format
     * (e.g. numeric) otherwise an exception should be thrown. This is
     * tested here.
     *
     * @return array
     */
    public static function invalidNoteListFilterProvider(): array
    {
        $exceptionMessage = 'Value has to be numeric.';

        return [
            [
                'filterQueryParams' => ['client_id' => ''],
                'exceptionMessage' => $exceptionMessage,
            ],
            [
                'filterQueryParams' => ['client_id' => 'abc'],
                'exceptionMessage' => $exceptionMessage,
            ],
            [
                'filterQueryParams' => ['most-recent' => 'abc'],
                'exceptionMessage' => $exceptionMessage,
            ],
            [
                'filterQueryParams' => ['user' => 'abc'],
                'exceptionMessage' => $exceptionMessage,
            ],
        ];
    }

    /**
     * Provides logged-in user and user linked to note with the expected result.
     * As the permissions are a lot more simple than for client for instance,
     * Create Update Delete cases are all in this function but if it should
     * get more complex, they should be split in different providers.
     *
     * @return array{
     *     array{
     *         userLinkedToNoteRow: array,
     *         authenticatedUserRow: array,
     *         expectedResult: array{
     *             creation: array,
     *             modification: array,
     *             deletion: array,
     *         },
     *     },
     * }
     */
    public static function noteCreateUpdateDeleteProvider(): array
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
            'dbChanged' => true,
            'jsonResponse' => [
                'status' => 'success',
                'data' => null,
            ],
        ];
        $unauthorizedResult = [
            StatusCodeInterface::class => StatusCodeInterface::STATUS_FORBIDDEN,
            'dbChanged' => false,
            'jsonResponse' => [
                'status' => 'error',
                'message' => 'Not allowed to change note.',
            ],
        ];
        $unauthorizedUpdateResult = $unauthorizedResult;
        $unauthorizedUpdateResult['jsonResponse']['message'] = 'Not allowed to change note.';
        $unauthorizedDeleteResult = $unauthorizedResult;
        $unauthorizedDeleteResult['jsonResponse']['message'] = 'Not allowed to delete note.';
        $authorizedCreateResult = [StatusCodeInterface::class => StatusCodeInterface::STATUS_CREATED];

        return [
            [ // ? newcomer not owner
                // User to whom the note (or client for creation) is linked (owner)
                'linkedUserRow' => $advisorAttributes,
                'authenticatedUserRow' => $newcomerAttributes,
                'expectedResult' => [
                    // Allowed to create note on client where user is not owner
                    'creation' => $authorizedCreateResult,
                    'modification' => [
                        'mainNote' => $unauthorizedUpdateResult,
                        'normalNote' => $unauthorizedResult,
                    ],
                    'deletion' => [
                        // Delete main note request is expected to produce 405 HttpMethodNotAllowedException
                        'normalNote' => $unauthorizedDeleteResult,
                    ],
                ],
            ],
            [ // ? newcomer owner
                // User to whom the note (or client for creation) is linked
                'linkedUserRow' => $newcomerAttributes,
                'authenticatedUserRow' => $newcomerAttributes,
                'expectedResult' => [
                    'creation' => [
                        StatusCodeInterface::class => StatusCodeInterface::STATUS_CREATED,
                    ],
                    'modification' => [
                        // Newcomer may not edit client basic data which has the same rights as the main note
                        'mainNote' => $unauthorizedUpdateResult,
                        'normalNote' => $authorizedResult,
                    ],
                    'deletion' => [
                        // Delete main note request is expected to produce 405 HttpMethodNotAllowedException
                        'normalNote' => $authorizedResult,
                    ],
                ],
            ],
            [ // ? advisor owner
                // User to whom the note (or client for creation) is linked
                'linkedUserRow' => $advisorAttributes,
                'authenticatedUserRow' => $advisorAttributes,
                'expectedResult' => [
                    'creation' => [ // Allowed to create note on client where user is not owner
                        StatusCodeInterface::class => StatusCodeInterface::STATUS_CREATED,
                    ],
                    'modification' => [
                        'mainNote' => $authorizedResult,
                        'normalNote' => $authorizedResult,
                    ],
                    'deletion' => [
                        // Delete main note request is expected to produce 405 HttpMethodNotAllowedException
                        'normalNote' => $authorizedResult,
                    ],
                ],
            ],
            [ // ? advisor not owner
                // User to whom the note (or client for creation) is linked
                'linkedUserRow' => $managingAdvisorAttributes,
                'authenticatedUserRow' => $advisorAttributes,
                'expectedResult' => [
                    'creation' => $authorizedCreateResult,
                    'modification' => [
                        'mainNote' => $authorizedResult,
                        'normalNote' => $unauthorizedUpdateResult,
                    ],
                    'deletion' => [
                        // Delete main note request is expected to produce 405 HttpMethodNotAllowedException
                        'normalNote' => $unauthorizedDeleteResult,
                    ],
                ],
            ],
            [ // ? managing advisor not owner
                // User to whom the note (or client for creation) is linked
                'linkedUserRow' => $advisorAttributes,
                'authenticatedUserRow' => $managingAdvisorAttributes,
                'expectedResult' => [
                    'creation' => $authorizedCreateResult,
                    'modification' => [
                        'mainNote' => $authorizedResult,
                        'normalNote' => $authorizedResult,
                    ],
                    'deletion' => [
                        // Delete main note request is expected to produce 405 HttpMethodNotAllowedException
                        'normalNote' => $authorizedResult,
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
