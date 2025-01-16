<?php

namespace App\Test\TestCase\Note\Provider;

use App\Module\User\Enum\UserRole;
use Fig\Http\Message\StatusCodeInterface;

class NoteCreateUpdateDeleteProvider
{

    /**
     * Provides logged-in user and user linked to note with the expected result.
     * As the permissions are a lot more simple than for client, for instance,
     * Create Update Delete cases are all in this function, but if it should
     * get more complex, they should be split in different providers.
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
}
