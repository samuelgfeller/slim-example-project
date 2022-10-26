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
        // Set different user role attributes
        $managingAdvisorRow = ['user_role_id' => 2];
        $advisorRow = ['user_role_id' => 3];
        $newcomerRow = ['user_role_id' => 4];

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
                'user_linked_to_client' => $newcomerRow,
                'authenticated_user' => $newcomerRow,
                'data_to_be_changed' => ['first_name' => 'value'],
                'expected_result' => $unauthorizedResult
            ],
            // * Advisor
            [ // ? Advisor owner - data to be changed allowed
                'user_linked_to_client' => $advisorRow,
                'authenticated_user' => $advisorRow,
                'data_to_be_changed' => array_merge(['client_status_id' => 'new'], $basicClientDataChanges),
                'expected_result' => $authorizedResult,
            ],
            [ // ? Advisor owner - data to be changed not allowed
                'user_linked_to_client' => $advisorRow,
                'authenticated_user' => $advisorRow,
                'data_to_be_changed' => ['user_id' => 'new'],
                'expected_result' => $unauthorizedResult,
            ],
            [ // ? Advisor not owner - data to be changed allowed
                'user_linked_to_client' => $managingAdvisorRow,
                'authenticated_user' => $advisorRow,
                'data_to_be_changed' => $basicClientDataChanges,
                'expected_result' => $authorizedResult,
            ],
            [ // ? Advisor not owner - data to be changed not allowed
                'user_linked_to_client' => $managingAdvisorRow,
                'authenticated_user' => $advisorRow,
                'data_to_be_changed' => ['client_status_id' => 'new'],
                'expected_result' => $unauthorizedResult,
            ],

            // * Managing advisor
            [ // ? Managing advisor not owner - there is no data change that is not allowed for managing advisor
                'user_linked_to_client' => $advisorRow,
                'authenticated_user' => $managingAdvisorRow,
                'data_to_be_changed' => array_merge(
                    $basicClientDataChanges,
                    ['client_status_id' => 'new', 'user_id' => 'new']
                ),
                'expected_result' => $authorizedResult,
            ],

        ];
    }
}