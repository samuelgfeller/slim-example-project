<?php

namespace App\Test\Provider\Client;

use App\Test\Fixture\FixtureTrait;
use App\Test\Fixture\UserFixture;
use Fig\Http\Message\StatusCodeInterface;

class ClientDeleteCaseProvider
{
    use FixtureTrait;
    /**
     * @return array[]
     */
    public function provideUsersForClientDelete(): array
        {
            // Get users with different roles
            $managingAdvisorData = $this->getFixtureRecordsWithAttributes(['user_role_id' => 2], UserFixture::class);
            $advisorData = $this->getFixtureRecordsWithAttributes(['user_role_id' => 3], UserFixture::class);
            $newcomerData = $this->getFixtureRecordsWithAttributes(['user_role_id' => 4], UserFixture::class);

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
                    'user_linked_to_client' => $newcomerData,
                    'authenticated_user' => $newcomerData,
                    'expected_result' => $unauthorizedResult
                ],
                // * Advisor
                [ // ? Advisor owner - not allowed
                    'user_linked_to_client' => $advisorData,
                    'authenticated_user' => $advisorData,
                    'expected_result' => $unauthorizedResult,
                ],
                // * Managing advisor
                [ // ? Managing advisor not owner - allowed
                    'user_linked_to_client' => $advisorData,
                    'authenticated_user' => $managingAdvisorData,
                    'expected_result' => $authorizedResult,
                ],

            ];
        }
}