<?php

namespace App\Test\Traits;

use App\Domain\User\Enum\UserRole;
use App\Infrastructure\Authentication\UserRoleFinderRepository;
use App\Test\Fixture\UserFixture;

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
        // If user role is provided and is instance of UserRole, replace array key with the actual id
        if ($userAttr['user_role_id'] ?? '' instanceof UserRole) {
            $userAttr['user_role_id'] = $this->getUserRoleIdByEnum($userAttr['user_role_id']);
        }

        return $userAttr;
    }

    /**
     * Returns user role id from given Enum case.
     *
     * @param UserRole $userRole
     *
     * @return int
     */
    protected function getUserRoleIdByEnum(UserRole $userRole): int
    {
        $userRoleFinderRepository = $this->container->get(UserRoleFinderRepository::class);

        return $userRoleFinderRepository->findUserRoleIdByName($userRole->value);
    }

    /**
     * Change array of UserRole Enum cases to expected availableUserRoles
     * array from the server with id and capitalized role name [{id} => {Role name}].
     *
     * @param array $userRoles user roles with Enum cases array
     *
     * @return array
     */
    protected function formatAvailableUserRoles(array $userRoles): array
    {
        $formattedRoles = [];
        foreach ($userRoles as $userRole) {
            // Key is role id and value the name for the dropdown
            $formattedRoles[$this->getUserRoleIdByEnum($userRole)] = $userRole->roleNameForDropdown();
        }

        return $formattedRoles;
    }

    /**
     * Takes tested and authenticated user as reference in the form of attributes
     * like ['user_role_id' => UserRole::Advisor, 'first_name' => 'John']
     * where the UserRole enum is replaced by the actual user role id and
     * both of the users are inserted (unless equal) attributing the inserted
     * user data values to the arguments reference passed meaning that the
     * variables where the function was called will change values.
     * That's why no return value is needed.
     *
     * @param array $userAttr user attributes reference that will be changed into the inserted user data
     * @param array $authenticatedUserAttr user attributes reference that will be changed to the inserted user data
     */
    protected function insertUserFixturesWithAttributes(array &$userAttr, array &$authenticatedUserAttr): void
    {
        $authenticatedUserAttrOriginal = $authenticatedUserAttr;
        // Insert authenticated user and user linked to resource with given attributes containing the user role
        $authenticatedUserAttr = $this->insertFixturesWithAttributes(
            $this->addUserRoleId($authenticatedUserAttr),
            UserFixture::class
        );
        if ($authenticatedUserAttrOriginal === $userAttr) {
            $userAttr = $authenticatedUserAttr;
        } else {
            // If authenticated user and owner user is not the same, insert owner
            $userAttr = $this->insertFixturesWithAttributes(
                $this->addUserRoleId($userAttr),
                UserFixture::class
            );
        }
    }
}
