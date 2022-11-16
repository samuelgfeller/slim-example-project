<?php

namespace App\Test\Traits;

use App\Domain\User\Enum\UserRole;
use App\Infrastructure\Authentication\UserRoleFinderRepository;

trait AuthorizationTestTrait
{

    /**
     * Adds the correct user role id to given attributes containing
     * UserRole enum case.
     *
     * @param array $userAttr
     *
     * @return array with all user attributes including of course user_role_id if set
     */
    protected function addUserRoleId(array $userAttr): array
    {
        $userRoleFinderRepository = $this->container->get(UserRoleFinderRepository::class);
        // If user role is provided and is instance of UserRole, replace array key with the actual id
        if ($userAttr['user_role_id'] ?? '' instanceof UserRole) {
            $userAttr['user_role_id'] = $userRoleFinderRepository->findUserRoleIdByName(
                $userAttr['user_role_id']->value
            );
        }
        return $userAttr;
    }
}