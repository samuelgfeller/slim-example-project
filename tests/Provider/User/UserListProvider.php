<?php

namespace App\Test\Provider\User;

use App\Modules\Authorization\Enum\Privilege;
use App\Modules\User\Enum\UserRole;
use Fig\Http\Message\StatusCodeInterface;

class UserListProvider
{
    /**
     * Different combinations of user logged-in users and owner user requesting to fetch users.
     * One authenticated user and one other is tested at a time is tested for clarity and simplicity.
     */
    public static function userListAuthorizationCases(): array
    {
        // Set different user role attributes. The following function is needed in the test function to add user role id
        // $this->insertUserFixtures($userData, $authenticatedUserData);
        $adminAttr = ['user_role_id' => UserRole::ADMIN];
        $managingAdvisorAttr = ['user_role_id' => UserRole::MANAGING_ADVISOR];
        $advisorAttr = ['user_role_id' => UserRole::ADVISOR];
        $newcomerAttr = ['user_role_id' => UserRole::NEWCOMER];

        // Testing authorization: with the lowest allowed privilege and with the highest not allowed
        return [ // User owner is the user itself
            [// ? advisor owner and newcomer other - not allowed to read other - only allowed to read own status and role
                'userRow' => $newcomerAttr,
                'authenticatedUserRow' => $advisorAttr,
                'expectedResult' => [
                    StatusCodeInterface::class => StatusCodeInterface::STATUS_OK,
                    // Each owner (authenticated user) is allowed to read his user data
                    'own' => [
                        'statusPrivilege' => Privilege::R,
                        'userRolePrivilege' => Privilege::R,
                        'availableUserRoles' => [UserRole::ADVISOR],
                    ],
                    'other' => false,
                ],
            ],
            [// ? managing advisor and advisor other - allowed to read and update own status but not available roles -
                // ? allowed to read and update other status and limited user role
                'userRow' => $advisorAttr,
                'authenticatedUserRow' => $managingAdvisorAttr,
                'expectedResult' => [
                    StatusCodeInterface::class => StatusCodeInterface::STATUS_OK,
                    'own' => [
                        'statusPrivilege' => Privilege::CRUD,
                        'userRolePrivilege' => Privilege::R,
                        'availableUserRoles' => [UserRole::MANAGING_ADVISOR],
                    ],
                    'other' => [
                        'statusPrivilege' => Privilege::CRUD,
                        'userRolePrivilege' => Privilege::CRU,
                        'availableUserRoles' => [UserRole::ADVISOR, UserRole::NEWCOMER],
                    ],
                ],
            ],
            [// ? managing advisor - other is also managing advisor - allowed to read but not change anything on other
                'userRow' => ['user_role_id' => UserRole::MANAGING_ADVISOR, 'first_name' => 'Josh'],
                'authenticatedUserRow' => $managingAdvisorAttr,
                'expectedResult' => [
                    StatusCodeInterface::class => StatusCodeInterface::STATUS_OK,
                    'own' => [
                        'statusPrivilege' => Privilege::CRUD,
                        'userRolePrivilege' => Privilege::R,
                        'availableUserRoles' => [UserRole::MANAGING_ADVISOR],
                    ],
                    'other' => [
                        'statusPrivilege' => Privilege::R,
                        'userRolePrivilege' => Privilege::R,
                        'availableUserRoles' => [UserRole::MANAGING_ADVISOR],
                    ],
                ],
            ],
            [// ? admin not owner - everything allowed even to other admins
                'userRow' => ['user_role_id' => UserRole::ADMIN, 'first_name' => 'Josh'],
                'authenticatedUserRow' => $adminAttr,
                'expectedResult' => [
                    StatusCodeInterface::class => StatusCodeInterface::STATUS_OK,
                    'own' => [
                        'statusPrivilege' => Privilege::CRUD,
                        'userRolePrivilege' => Privilege::CRU,
                        'availableUserRoles' => [
                            UserRole::ADMIN,
                            UserRole::MANAGING_ADVISOR,
                            UserRole::ADVISOR,
                            UserRole::NEWCOMER,
                        ],
                    ],
                    'other' => [
                        'statusPrivilege' => Privilege::CRUD,
                        'userRolePrivilege' => Privilege::CRU,
                        'availableUserRoles' => [
                            UserRole::ADMIN,
                            UserRole::MANAGING_ADVISOR,
                            UserRole::ADVISOR,
                            UserRole::NEWCOMER,
                        ],
                    ],
                ],
            ],
        ];
    }
}
