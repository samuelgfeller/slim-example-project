<?php

namespace App\Test\Provider\User;

use App\Modules\User\Enum\UserRole;
use Fig\Http\Message\StatusCodeInterface;

class UserDeleteProvider
{
    /**
     * @return array[]
     */
    public static function userDeleteAuthorizationCases(): array
    {
        // Get users with different roles
        $managingAdvisorAttributes = ['user_role_id' => UserRole::MANAGING_ADVISOR];
        $otherManagingAdvisorAttributes = ['user_role_id' => UserRole::MANAGING_ADVISOR, 'first_name' => 'Elon'];
        $advisorAttributes = ['user_role_id' => UserRole::ADVISOR];

        $authorizedResult = [
            StatusCodeInterface::class => StatusCodeInterface::STATUS_OK,
            'dbChanged' => true,
            'jsonResponse' => [
                'status' => 'success',
            ],
        ];
        $unauthorizedResult = [
            StatusCodeInterface::class => StatusCodeInterface::STATUS_FORBIDDEN,
            'dbChanged' => false,
            'jsonResponse' => [
                'status' => 'error',
                'message' => 'Not allowed to delete user.',
            ],
        ];

        return [
            // * Advisor
            [ // ? Advisor owner - not allowed - advisors and newcomers cannot delete their account
                'userToDeleteRow' => $advisorAttributes,
                'authenticatedUserRow' => $advisorAttributes,
                'expectedResult' => $unauthorizedResult,
            ],
            // * Managing advisor
            [ // ? Managing advisor not owner - other is also managing advisor - not allowed
                'userToDeleteRow' => $otherManagingAdvisorAttributes,
                'authenticatedUserRow' => $managingAdvisorAttributes,
                'expectedResult' => $unauthorizedResult,
            ],
            [ // ? Managing advisor not owner - other is advisor - allowed
                'userToDeleteRow' => $advisorAttributes,
                'authenticatedUserRow' => $managingAdvisorAttributes,
                'expectedResult' => $authorizedResult,
            ],
            [ // ? Managing advisor owner - allowed
                'userToDeleteRow' => $managingAdvisorAttributes,
                'authenticatedUserRow' => $managingAdvisorAttributes,
                'expectedResult' => $authorizedResult,
            ],
            [ // ? Admin not owner - other is also admin - allowed
                'userToDeleteRow' => ['user_role_id' => UserRole::ADMIN, 'first_name' => 'Bill'],
                'authenticatedUserRow' => ['user_role_id' => UserRole::ADMIN],
                'expectedResult' => $authorizedResult,
            ],
        ];
    }
}
