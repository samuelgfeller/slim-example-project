<?php

namespace App\Test\Provider\Client;

use App\Domain\User\Enum\UserRole;
use Fig\Http\Message\StatusCodeInterface;

class ClientReadProvider
{
    public static function clientReadAuthorizationCases(): array
    {
        // Set different user role attributes
        $adminAttr = ['user_role_id' => UserRole::ADMIN];
        $managingAdvisorAttr = ['user_role_id' => UserRole::MANAGING_ADVISOR];
        $advisorAttr = ['user_role_id' => UserRole::ADVISOR];

        // Testing authorization: with the lowest allowed privilege and with highest not allowed
        return [ // User owner is the user itself
            [// ? advisor not owner - allowed to read undeleted client
                'userRow' => $managingAdvisorAttr,
                'authenticatedUserRow' => $advisorAttr,
                'clientIsDeleted' => false,
                'expectedStatusCode' => StatusCodeInterface::STATUS_OK,
            ],
            [// ? advisor owner - not allowed to read deleted client
                'userRow' => $advisorAttr,
                'authenticatedUserRow' => $advisorAttr,
                'clientIsDeleted' => true,
                'expectedStatusCode' => StatusCodeInterface::STATUS_FORBIDDEN,
            ],
            [// ? managing advisor not owner - allowed to read deleted client
                'userRow' => $adminAttr,
                'authenticatedUserRow' => $managingAdvisorAttr,
                'clientIsDeleted' => true,
                'expectedStatusCode' => StatusCodeInterface::STATUS_OK,
            ],
        ];
    }
}
