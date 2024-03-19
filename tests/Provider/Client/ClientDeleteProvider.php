<?php

namespace App\Test\Provider\Client;

use App\Domain\User\Enum\UserRole;
use Fig\Http\Message\StatusCodeInterface;

class ClientDeleteProvider
{
    /**
     * @return array[]
     */
    public static function clientDeleteUsersAndExpectedResultProvider(): array
    {
        // Get users with different roles
        $managingAdvisorAttributes = ['user_role_id' => UserRole::MANAGING_ADVISOR];
        $advisorAttributes = ['user_role_id' => UserRole::ADVISOR];
        $newcomerAttributes = ['user_role_id' => UserRole::NEWCOMER];

        $authorizedResult = [
            StatusCodeInterface::class => StatusCodeInterface::STATUS_OK,
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
                'message' => 'Not allowed to delete client.',
            ],
        ];

        // Permissions for deletion are quite simple: only managing advisors and higher may delete clients
        return [
            // * Newcomer
            [ // ? Newcomer owner - not allowed
                'userLinkedToClientRow' => $newcomerAttributes,
                'authenticatedUserRow' => $newcomerAttributes,
                'expectedResult' => $unauthorizedResult,
            ],
            // * Advisor
            [ // ? Advisor owner - not allowed
                'userLinkedToClientRow' => $advisorAttributes,
                'authenticatedUserRow' => $advisorAttributes,
                'expectedResult' => $unauthorizedResult,
            ],
            // * Managing advisor
            [ // ? Managing advisor not owner - allowed
                'userLinkedToClientRow' => $advisorAttributes,
                'authenticatedUserRow' => $managingAdvisorAttributes,
                'expectedResult' => $authorizedResult,
            ],
        ];
    }
}
