<?php

namespace App\Test\Provider\Client;

use App\Test\Fixture\FixtureTrait;
use App\Test\Fixture\UserFixture;
use Fig\Http\Message\StatusCodeInterface;

class ClientUpdateCaseProvider
{

    use FixtureTrait;

    /**
     * Client creation authorization
     * Provides combination of different user roles with expected result.
     * This tests the rules in ClientAuthorizationChecker.
     *
     * @return array[]
     */
    public function provideUsersAndExpectedResultForClientUpdate(): array
    {
        // Get users with the different roles
        $managingAdvisorData = $this->findRecordsFromFixtureWhere(['user_role_id' => 2], UserFixture::class)[0];
        $advisorData = $this->findRecordsFromFixtureWhere(['user_role_id' => 3], UserFixture::class)[0];
        $newcomerData = $this->findRecordsFromFixtureWhere(['user_role_id' => 4], UserFixture::class)[0];

        $expectedAuthorizedJsonResponse = [
            StatusCodeInterface::class => StatusCodeInterface::STATUS_OK,
            'db_changed' => true,
            'json_response' => [
                'status' => 'success',
                'data' => null,
            ],
        ];
        $expectedUnauthorizedJsonResponse = [
            StatusCodeInterface::class => StatusCodeInterface::STATUS_FORBIDDEN,
            'db_changed' => false,
            'json_response' => [
                'status' => 'error',
                'message' => 'Not allowed to update client.',
            ]
        ];

        $basicClientDataChanges = [
            'first_name' => 'NewFirstName',
            'last_name' => 'NewLastName',
            'birthdate' => '1999-10-22',
            'location' => 'NewLocation',
            'phone' => '011 111 11 11',
            'email' => 'new.email@test.ch',
            'sex' => 'O',
        ];

        // To avoid testing each column separately for each user role, the most basic change is taken to test
        // [foreign_key => 'new'] will be replaced in test function as user has to be added to the database
        return [
            // * Newcomer
            // User role and when "owner" is mentioned, it is always from the perspective of the authenticated user
            [ // ? Newcomer owner - data to be changed is the one with the least privilege needed - not allowed
                'user_linked_to_client' => $newcomerData,
                'authenticated_user' => $newcomerData,
                'data_to_be_changed' => ['first_name' => 'value'],
                'expected_result' => $expectedUnauthorizedJsonResponse
            ],
            // * Advisor
            [ // ? Advisor owner - data to be changed allowed
                'user_linked_to_client' => $advisorData,
                'authenticated_user' => $advisorData,
                'data_to_be_changed' => array_merge(['client_status_id' => 'new'], $basicClientDataChanges),
                'expected_result' => $expectedAuthorizedJsonResponse,
            ],
            [ // ? Advisor owner - data to be changed not allowed
                'user_linked_to_client' => $advisorData,
                'authenticated_user' => $advisorData,
                'data_to_be_changed' => ['user_id' => 'new'],
                'expected_result' => $expectedUnauthorizedJsonResponse,
            ],
            [ // ? Advisor not owner - data to be changed allowed
                'user_linked_to_client' => $managingAdvisorData,
                'authenticated_user' => $advisorData,
                'data_to_be_changed' => $basicClientDataChanges,
                'expected_result' => $expectedAuthorizedJsonResponse,
            ],
            [ // ? Advisor not owner - data to be changed not allowed
                'user_linked_to_client' => $managingAdvisorData,
                'authenticated_user' => $advisorData,
                'data_to_be_changed' => ['client_status_id' => 'new'],
                'expected_result' => $expectedUnauthorizedJsonResponse,
            ],

            // * Managing advisor
            [ // ? Managing advisor not owner - there is no data change that is not allowed for managing advisor
                'user_linked_to_client' => $advisorData,
                'authenticated_user' => $managingAdvisorData,
                'data_to_be_changed' => array_merge(
                    $basicClientDataChanges,
                    ['client_status_id' => 'new', 'user_id' => 'new']
                ),
                'expected_result' => $expectedAuthorizedJsonResponse,
            ],

        ];
    }
}