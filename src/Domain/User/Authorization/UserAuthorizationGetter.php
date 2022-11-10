<?php

namespace App\Domain\User\Authorization;

use App\Infrastructure\Authentication\UserRoleFinderRepository;

/**
 * The client should know when to display edit and delete icons
 * Admins can edit all notes, users only their own
 */
class UserAuthorizationGetter
{
    public function __construct(
        private readonly UserAuthorizationChecker $userAuthorizationChecker,
        private readonly UserRoleFinderRepository $userRoleFinderRepository,
    ) {
    }


    /**
     * Returns authorized user roles for given user
     *
     * Note: this is not performant at all as for each user all user roles changes
     * have to be tested and isGrantedToUpdate makes 4 sql requests each time meaning
     * that for 10 users and 4 roles and 4 requests in the function there will be
     * more than 120 sql requests so if optimisations have to be made, here is a good place
     * to start. It is like this for simplicity as there will not be a lot of users
     * anyway and the user list action is quite rare and limited to some users.
     *
     * @param int $userId
     * @param int|null $attributedUserRoleId
     * @return array Granted user roles. Should always contain at least already attributed user role
     */
    public function getAuthorizedUserRolesForUser(int $userId, ?int $attributedUserRoleId): array
    {
        $allUserRoles = $this->userRoleFinderRepository->findAllUserRoles();
        $grantedUserRoles = [];
        foreach ($allUserRoles as $id => $roleName) {
            // If the role is already attributed to user the value is added so that it's displayed in the template
            if ($id === $attributedUserRoleId ||
                // Test each role if user is allowed to update
                $this->userAuthorizationChecker->isGrantedToUpdate(['user_role_id' => $id], $userId)
            ) {
                $grantedUserRoles[$id] = $roleName;
            }
        }
        return $grantedUserRoles;
    }
}