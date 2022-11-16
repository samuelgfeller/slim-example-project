<?php

namespace App\Test\Provider\Client;

use App\Test\Traits\FixtureTestTrait;
use Fig\Http\Message\StatusCodeInterface;

class ClientDeleteCaseProvider
{
    use FixtureTestTrait;
    /**
     * @return array[]
     */
    public function provideUsersForClientDelete(): array
        {
            // Get users with different roles
            $managingAdvisorAttributes = ['user_role_id' => 2];
            $advisorAttributes = ['user_role_id' => 3];
            $newcomerAttributes = ['user_role_id' => 4];

            $authorizedResult = [
                StatusCodeInterface::class => StatusCodeInterface::STATUS_OK,
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
                    'message' => 'Not allowed to delete client.',
                ]
            ];

            // Permissions for deletion are quite simple: only managing advisors and higher may delete clients
            return [
                // * Newcomer
                [ // ? Newcomer owner - not allowed
                    'user_linked_to_client' => $newcomerAttributes,
                    'authenticated_user' => $newcomerAttributes,
                    'expected_result' => $unauthorizedResult
                ],
                // * Advisor
                [ // ? Advisor owner - not allowed
                    'user_linked_to_client' => $advisorAttributes,
                    'authenticated_user' => $advisorAttributes,
                    'expected_result' => $unauthorizedResult,
                ],
                // * Managing advisor
                [ // ? Managing advisor not owner - allowed
                    'user_linked_to_client' => $advisorAttributes,
                    'authenticated_user' => $managingAdvisorAttributes,
                    'expected_result' => $authorizedResult,
                ],

            ];
        }
}