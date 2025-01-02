<?php

namespace App\Test\Provider\Client;

use App\Modules\User\Enum\UserRole;
use Fig\Http\Message\StatusCodeInterface;

class ClientReadProvider
{
    public static function clientReadAuthorizationCases(): array
    {
        // Set different user role attributes
        $adminAttr = ['user_role_id' => UserRole::ADMIN];
        $managingAdvisorAttr = ['user_role_id' => UserRole::MANAGING_ADVISOR];
        $advisorAttr = ['user_role_id' => UserRole::ADVISOR];
        $newcomerAttr = ['user_role_id' => UserRole::NEWCOMER];

        // Testing authorization: with the lowest allowed privilege and with the highest not allowed
        return [ // User owner is the user itself
            [// ? newcomer not owner - allowed reading undeleted client - but not update or create main note
                'userRow' => $managingAdvisorAttr,
                'authenticatedUserRow' => $newcomerAttr,
                'clientIsDeleted' => false,
                'expectedStatusCode' => StatusCodeInterface::STATUS_OK,
            ],
            [// ? advisor not owner - allowed reading undeleted client and update and create main note
                'userRow' => $managingAdvisorAttr,
                'authenticatedUserRow' => $advisorAttr,
                'clientIsDeleted' => false,
                'expectedStatusCode' => StatusCodeInterface::STATUS_OK,
            ],
            [// ? advisor owner - not allowed reading deleted client
                'userRow' => $advisorAttr,
                'authenticatedUserRow' => $advisorAttr,
                'clientIsDeleted' => true,
                'expectedStatusCode' => StatusCodeInterface::STATUS_FORBIDDEN,
            ],
            [// ? managing advisor not owner - allowed reading deleted client
                'userRow' => $adminAttr,
                'authenticatedUserRow' => $managingAdvisorAttr,
                'clientIsDeleted' => true,
                'expectedStatusCode' => StatusCodeInterface::STATUS_OK,
            ],
        ];
    }
}
