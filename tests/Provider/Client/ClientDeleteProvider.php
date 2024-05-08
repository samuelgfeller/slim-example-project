<?php

namespace App\Test\Provider\Client;

use App\Domain\User\Enum\UserRole;
use Fig\Http\Message\StatusCodeInterface;

class ClientDeleteProvider
{
    /**
     * @return array[]
     */
    public static function clientDeleteProvider(): array
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

        // Only managing advisors and higher may delete clients
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

    public static function clientUndeleteDeleteProvider(): array
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
                'message' => 'Not allowed to update client.',
            ],
        ];

        // Only managing advisors and higher may delete clients
        return [
            // * Advisor
            'advisor owner undelete' => [ // ? Advisor owner - undelete client - not allowed
                'userLinkedToClientRow' => $newcomerAttributes,
                'authenticatedUserRow' => $advisorAttributes,
                // Data to be changed
                'requestData' => ['deleted_at' => null],
                'expectedResult' => $unauthorizedResult,
            ],
            // * Managing advisor
            'managing advisor not owner undelete' => [ // ? Managing advisor not owner - undelete client - allowed
                'userLinkedToClientRow' => $advisorAttributes,
                'authenticatedUserRow' => $managingAdvisorAttributes,
                // Data to be changed
                'requestData' => ['deleted_at' => null],
                'expectedResult' => $authorizedResult,
            ],
        ];
    }
}
