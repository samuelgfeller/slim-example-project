<?php

namespace App\Test\Provider\User;

use App\Domain\User\Enum\UserRole;
use Fig\Http\Message\StatusCodeInterface;

class UserDeleteProvider
{
    /**
     * @return array[]
     */
    public function userDeleteAuthorizationCases(): array
    {
        // Get users with different roles
        $managingAdvisorAttributes = ['user_role_id' => UserRole::MANAGING_ADVISOR];
        $otherManagingAdvisorAttributes = ['user_role_id' => UserRole::MANAGING_ADVISOR, 'first_name' => 'Elon'];
        $advisorAttributes = ['user_role_id' => UserRole::ADVISOR];

        $authorizedResult = [
            StatusCodeInterface::class => StatusCodeInterface::STATUS_OK,
            'db_changed' => true,
            'json_response' => [
                'status' => 'success',
            ],
        ];
        $unauthorizedResult = [
            StatusCodeInterface::class => StatusCodeInterface::STATUS_FORBIDDEN,
            'db_changed' => false,
            'json_response' => [
                'status' => 'error',
                'message' => 'Not allowed to delete user.',
            ],
        ];

        return [
            // * Advisor
            [ // ? Advisor owner - not allowed - advisors and newcomers cannot delete their account
                'user_to_delete' => $advisorAttributes,
                'authenticated_user' => $advisorAttributes,
                'expected_result' => $unauthorizedResult,
            ],
            // * Managing advisor
            [ // ? Managing advisor not owner - other is also managing advisor - not allowed
                'user_to_delete' => $otherManagingAdvisorAttributes,
                'authenticated_user' => $managingAdvisorAttributes,
                'expected_result' => $unauthorizedResult,
            ],
            [ // ? Managing advisor not owner - other is advisor - allowed
                'user_to_delete' => $advisorAttributes,
                'authenticated_user' => $managingAdvisorAttributes,
                'expected_result' => $authorizedResult,
            ],
            [ // ? Managing advisor owner - allowed
                'user_to_delete' => $managingAdvisorAttributes,
                'authenticated_user' => $managingAdvisorAttributes,
                'expected_result' => $authorizedResult,
            ],
            [ // ? Admin not owner - other is also admin - allowed
                'user_to_delete' => ['user_role_id' => UserRole::ADMIN, 'first_name' => 'Bill'],
                'authenticated_user' => ['user_role_id' => UserRole::ADMIN],
                'expected_result' => $authorizedResult,
            ],
        ];
    }
}
