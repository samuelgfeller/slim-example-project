<?php

namespace App\Test\Trait;

use App\Module\Authorization\Repository\AuthorizationUserRoleFinderRepository;
use App\Module\User\Enum\UserRole;
use App\Test\Fixture\UserFixture;

trait AuthorizationTestTrait
{
    /**
     * Returns user role id from given Enum case.
     *
     * @param UserRole $userRole
     *
     * @return int
     */
    protected function getUserRoleIdByEnum(UserRole $userRole): int
    {
        $authorizationUserRoleFinderRepository = $this->container->get(AuthorizationUserRoleFinderRepository::class);

        return $authorizationUserRoleFinderRepository->findUserRoleIdByName($userRole->value);
    }

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
        if (isset($userAttr['user_role_id']) && $userAttr['user_role_id'] instanceof UserRole) {
            $userAttr['user_role_id'] = $this->getUserRoleIdByEnum($userAttr['user_role_id']);
        }

        return $userAttr;
    }

    /**
     * Inserts user fixtures and changes the given user attributes to the inserted user data.
     * If both user arguments are different, it inserts both users; if same only one.
     * Then populates the given arguments by reference with the newly inserted
     * user attributes.
     * Takes two users attributes, one belongs to the user currently under test and
     * the other to the authenticated user.
     * Example attributes for a user ['user_role_id' => UserRole::Advisor, 'first_name' => 'John']
     * The UserRole enum is replaced by the actual user role id and both of the users are inserted (unless equal).
     * Passed argument variables $authenticatedUserAttr and $userAttr are changed by reference with database
     * values.
     * This means that the variables in the original function will change after this function
     * has been called.
     * No return value is needed.
     *
     * @param array $authenticatedUserAttr user attributes reference that will be changed to the inserted user data
     * @param array|null $userAttr user attributes reference that will be changed into the inserted user data
     */
    protected function insertUserFixtures(array &$authenticatedUserAttr, ?array &$userAttr): void
    {
        $authenticatedUserAttrOriginal = $authenticatedUserAttr;
        // Insert authenticated user and user linked to resource with given attributes containing the user role
        $authenticatedUserAttr = $this->insertFixture(
            UserFixture::class,
            $this->addUserRoleId($authenticatedUserAttr),
        );
        if ($authenticatedUserAttrOriginal === $userAttr) {
            $userAttr = $authenticatedUserAttr;
        } // If userAttr is null, change array to contain array key "id" prevent the need of null checking later
        elseif ($userAttr === null) {
            $userAttr['id'] = null;
        } else {
            // If authenticated user and owner user is not the same, insert owner
            $userAttr = $this->insertFixture(
                UserFixture::class,
                $this->addUserRoleId($userAttr),
            );
        }
    }
}
