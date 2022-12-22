<?php

namespace App\Test\Provider\User;

use App\Domain\Authorization\Privilege;
use App\Domain\User\Enum\UserRole;
use Fig\Http\Message\StatusCodeInterface;

class UserListProvider
{
    /**
     * Different combinations of user logged-in users and owner user requesting to fetch users.
     * One authenticated user and one other is tested at a time is tested for clarity and simplicity.
     */
    public function userListAuthorizationCases(): array
    {
        // Set different user role attributes. The following function is needed in the test function to add user role id
        // $this->insertUserFixturesWithAttributes($userData, $authenticatedUserData);
        $adminAttr = ['user_role_id' => UserRole::ADMIN];
        $managingAdvisorAttr = ['user_role_id' => UserRole::MANAGING_ADVISOR];
        $advisorAttr = ['user_role_id' => UserRole::ADVISOR];
        $newcomerAttr = ['user_role_id' => UserRole::NEWCOMER];

        // General testing rule: test allowed with the lowest privilege and not allowed with highest not allowed
        return [ // User owner is the user itself
            [// ? advisor owner and newcomer other - not allowed to read other - only allowed to read own status and role
                'other_user' => $newcomerAttr,
                'authenticated_user' => $advisorAttr,
                'expected_result' => [
                    StatusCodeInterface::class => StatusCodeInterface::STATUS_OK,
                    // Each owner (authenticated user) is allowed to read his user data
                    'own' => [
                        'statusPrivilege' => Privilege::READ,
                        'userRolePrivilege' => Privilege::READ,
                        'availableUserRoles' => [UserRole::ADVISOR],
                    ],
                    'other' => false,
                ],
            ],
            [// ? managing advisor and advisor other - allowed to read and update own status but not available roles -
                // ? allowed to read and update other status and limited user role
                'other_user' => $advisorAttr,
                'authenticated_user' => $managingAdvisorAttr,
                'expected_result' => [
                    StatusCodeInterface::class => StatusCodeInterface::STATUS_OK,
                    'own' => [
                        'statusPrivilege' => Privilege::DELETE,
                        'userRolePrivilege' => Privilege::READ,
                        'availableUserRoles' => [UserRole::MANAGING_ADVISOR],
                    ],
                    'other' => [
                        'statusPrivilege' => Privilege::DELETE,
                        'userRolePrivilege' => Privilege::UPDATE,
                        'availableUserRoles' => [UserRole::ADVISOR, UserRole::NEWCOMER],
                    ],
                ],
            ],
            [// ? managing advisor - other is also managing advisor - allowed to read but not change anything on other
                'other_user' => ['user_role_id' => UserRole::MANAGING_ADVISOR, 'first_name' => 'Josh'],
                'authenticated_user' => $managingAdvisorAttr,
                'expected_result' => [
                    StatusCodeInterface::class => StatusCodeInterface::STATUS_OK,
                    'own' => [
                        'statusPrivilege' => Privilege::DELETE,
                        'userRolePrivilege' => Privilege::READ,
                        'availableUserRoles' => [UserRole::MANAGING_ADVISOR],
                    ],
                    'other' => [
                        'statusPrivilege' => Privilege::READ,
                        'userRolePrivilege' => Privilege::READ,
                        'availableUserRoles' => [UserRole::MANAGING_ADVISOR],
                    ],
                ],
            ],
            [// ? admin not owner - everything allowed even to other admins
                'other_user' => ['user_role_id' => UserRole::ADMIN, 'first_name' => 'Josh'],
                'authenticated_user' => $adminAttr,
                'expected_result' => [
                    StatusCodeInterface::class => StatusCodeInterface::STATUS_OK,
                    'own' => [
                        'statusPrivilege' => Privilege::DELETE,
                        'userRolePrivilege' => Privilege::UPDATE,
                        'availableUserRoles' => [
                            UserRole::ADMIN,
                            UserRole::MANAGING_ADVISOR,
                            UserRole::ADVISOR,
                            UserRole::NEWCOMER,
                        ],
                    ],
                    'other' => [
                        'statusPrivilege' => Privilege::DELETE,
                        'userRolePrivilege' => Privilege::UPDATE,
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
