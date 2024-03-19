<?php

namespace App\Test\Provider\User;

use App\Domain\User\Enum\UserRole;
use Fig\Http\Message\StatusCodeInterface;

class UserReadProvider
{
    /**
     * Provides authenticated and other user which is requested to be read.
     * Only status code can be asserted as expected result as page is rendered
     * by the server, and we can't test a rendered template.
     */
    public static function userReadAuthorizationCases(): array
    {
        // Set different user role attributes
        $adminAttr = ['user_role_id' => UserRole::ADMIN];
        $managingAdvisorAttr = ['user_role_id' => UserRole::MANAGING_ADVISOR];
        $advisorAttr = ['user_role_id' => UserRole::ADVISOR];
        $newcomerAttr = ['user_role_id' => UserRole::NEWCOMER];

        // General testing rule: test allowed with the lowest privilege and not allowed with highest not allowed
        return [ // User owner is the user itself
            [// ? newcomer owner - other is same user - allowed to read own
                'userRow' => $newcomerAttr,
                'authenticatedUserRow' => $newcomerAttr,
                'expectedResult' => [StatusCodeInterface::class => StatusCodeInterface::STATUS_OK],
            ],
            [// ? advisor owner - other is newcomer - not allowed to read other
                'userRow' => $newcomerAttr,
                'authenticatedUserRow' => $advisorAttr,
                'expectedResult' => [StatusCodeInterface::class => StatusCodeInterface::STATUS_FORBIDDEN],
            ],
            [// ? managing advisor - other also managing advisor other - allowed to read
                'userRow' => ['user_role_id' => UserRole::MANAGING_ADVISOR, 'first_name' => 'Josh'],
                'authenticatedUserRow' => $managingAdvisorAttr,
                'expectedResult' => [StatusCodeInterface::class => StatusCodeInterface::STATUS_OK],
            ],
            [// ? managing advisor - other is admin - allowed to read
                'userRow' => $adminAttr,
                'authenticatedUserRow' => $managingAdvisorAttr,
                'expectedResult' => [StatusCodeInterface::class => StatusCodeInterface::STATUS_OK],
            ],
        ];
    }
}
