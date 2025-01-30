<?php

namespace App\Test\TestCase\Note\List;

use App\Module\Authorization\Enum\Privilege;
use App\Module\User\Enum\UserRole;

class NoteListProvider
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
            // * No filter
            [
                'filterQueryParams' => [],
                // No users should be returned
                'expectedNotesWhereString' => 'FALSE',
                'usersAttrToInsert' => $usersToInsert,
                'clientAttrToInsert' => $clientToInsert,
                'notesAttrToInsert' => $notesToInsert,
            ],
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
}
