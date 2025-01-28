<?php

namespace App\Module\User\Update\Service;

use App\Application\Data\UserNetworkSessionData;
use App\Module\Authorization\Repository\AuthorizationUserRoleFinderRepository;
use App\Module\User\AssignRole\Service\UserAssignRoleAuthorizationChecker;
use App\Module\User\Enum\UserRole;
use App\Module\User\Update\Repository\UserUpdateAuthorizationRoleFinderRepository;
use Psr\Log\LoggerInterface;

/**
 * Check if authenticated user is permitted to do actions
 * Roles: newcomer < advisor < managing_advisor < administrator.
 */
final class UserUpdateAuthorizationChecker
{
    private ?int $loggedInUserId = null;

    public function __construct(
        private readonly UserNetworkSessionData $userNetworkSessionData,
        private readonly AuthorizationUserRoleFinderRepository $authorizationUserRoleFinderRepository,
        private readonly UserUpdateAuthorizationRoleFinderRepository $userAuthorizationRoleFinderRepository,
        private readonly UserAssignRoleAuthorizationChecker $userAssignRoleAuthorizationChecker,
        private readonly LoggerInterface $logger,
    ) {
        // Fix error $userId must not be accessed before initialization
        $this->loggedInUserId = $this->userNetworkSessionData->userId ?? null;
    }

    /**
     * Logic to check if logged-in user is granted to update user.
     * This function has a high cyclomatic complexity due to the many if-statements,
     * but for now I find it more readable than splitting it up into multiple functions.
     *
     * @param array $userDataToUpdate validated array with as key the column to
     * update and value the new value. There may be one or multiple entries,
     * depending on what the user wants to update
     * @param string|int $userIdToUpdate
     * @param bool $log log if forbidden (expected false when function is called for privilege setting)
     *
     * @return bool
     */
    public function isGrantedToUpdate(array $userDataToUpdate, string|int $userIdToUpdate, bool $log = true): bool
    {
        // Unset key id from data to update as is present in the array without the intention of being changed
        unset($userDataToUpdate['id']);
        if ($this->loggedInUserId === null) {
            $this->logger->error(
                'loggedInUserId not while user update authorization check' .
                json_encode($userDataToUpdate, JSON_PARTIAL_OUTPUT_ON_ERROR)
            );

            return false;
        }
        $grantedUpdateKeys = [];

        $authenticatedUserRoleHierarchy = $this->authorizationUserRoleFinderRepository->getRoleHierarchyByUserId(
            $this->loggedInUserId
        );
        $userToUpdateRoleData = $this->userAuthorizationRoleFinderRepository->getUserRoleDataFromUser((int)$userIdToUpdate);
        // Returns array with role name as key and hierarchy as value ['role_name' => hierarchy_int]
        // * Lower hierarchy number means higher privileged role
        $userRoleHierarchies = $this->authorizationUserRoleFinderRepository->getUserRolesHierarchies();

        // Only managing advisor or higher privileged can change users
        if ((($authenticatedUserRoleHierarchy <= $userRoleHierarchies[UserRole::MANAGING_ADVISOR->value]
                    // but only if user to change is advisor or lower
                    && $userToUpdateRoleData->hierarchy >= $userRoleHierarchies[UserRole::ADVISOR->value])
                // if user role is higher privileged than managing advisor (admin) -> authorized
                || $authenticatedUserRoleHierarchy <= $userRoleHierarchies[UserRole::ADMIN->value])
            // or if the user edits his own profile, also authorized to the next section
            || $this->loggedInUserId === (int)$userIdToUpdate
        ) {
            // Things that managing advisor and owner user are allowed to change
            // Personal info are values such as first name, last name and email
            $grantedUpdateKeys[] = 'personal_info';
            $grantedUpdateKeys[] = 'first_name';
            $grantedUpdateKeys[] = 'last_name';
            $grantedUpdateKeys[] = 'email';
            $grantedUpdateKeys[] = 'password_hash';
            $grantedUpdateKeys[] = 'theme';
            $grantedUpdateKeys[] = 'language';
            // If a new basic data field is added, it has to be added to provider userUpdateAuthorizationCases()
            // $basicDataChanges variable and invalid value to provider invalidUserUpdateCases()

            // Things that only managing_advisor and higher privileged are allowed to change
            // If the user is managing advisor we know by the parent if-statement
            // that the user to change has not higher role than advisor
            if ($authenticatedUserRoleHierarchy <= $userRoleHierarchies[UserRole::MANAGING_ADVISOR->value]) {
                $grantedUpdateKeys[] = 'status';

                // Check if the authenticated user is granted to attribute role if that's requested
                if (array_key_exists('user_role_id', $userDataToUpdate)
                    && $this->userAssignRoleAuthorizationChecker->userRoleIsGranted(
                        $userDataToUpdate['user_role_id'],
                        $userToUpdateRoleData->id,
                        $authenticatedUserRoleHierarchy,
                        $userRoleHierarchies
                    ) === true) {
                    $grantedUpdateKeys[] = 'user_role_id';
                }

                // There is a special case with passwords where the user can change his own password, but he needs to
                // provide the old password. If password_without_verification is added to $grantedUpdateKeys it means
                // that the authenticated user can change the password without the old password.
                // But if the user wants to change his own password, the old password is required regardless of role
                // so that nobody can change his password if the computer is left unattended and logged-in
                // https://security.stackexchange.com/a/24292 - to change other passwords it would be best if
                // the authenticated managing_advisor / admin password is asked instead of the old user password,
                // but this is too much complexity for this project.
                if ($this->loggedInUserId !== (int)$userIdToUpdate) {
                    $grantedUpdateKeys[] = 'password_without_verification';
                }
            }
            // Owner user (profile edit) is not allowed to change its user role or status
        }

        // Check that the data that the user wanted to update is in $grantedUpdateKeys array
        foreach ($userDataToUpdate as $key => $value) {
            // If at least one array key doesn't exist in $grantedUpdateKeys it means that user is not permitted
            if (!in_array($key, $grantedUpdateKeys, true)) {
                if ($log === true) {
                    $this->logger->notice(
                        'User ' . $this->loggedInUserId . ' tried to update user but isn\'t allowed to change' .
                        $key . ' to "' . $value . '".'
                    );
                }

                return false;
            }
        }

        // All keys in $userDataToUpdate are in $grantedUpdateKeys
        return true;
    }
}
