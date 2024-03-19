<?php

namespace App\Domain\User\Service\Authorization;

use App\Application\Data\UserNetworkSessionData;
use App\Domain\Authentication\Repository\UserRoleFinderRepository;
use App\Domain\User\Enum\UserRole;
use Psr\Log\LoggerInterface;

/**
 * Check if authenticated user is permitted to do actions
 * Roles: newcomer < advisor < managing_advisor < administrator.
 */
class UserPermissionVerifier
{
    private ?int $loggedInUserId = null;

    public function __construct(
        private readonly UserNetworkSessionData $userNetworkSessionData,
        private readonly UserRoleFinderRepository $userRoleFinderRepository,
        private readonly LoggerInterface $logger,
    ) {
        // Fix error $userId must not be accessed before initialization
        $this->loggedInUserId = $this->userNetworkSessionData->userId ?? null;
    }

    /**
     * Check if the authenticated user is allowed to create
     * Important to have user role in the object.
     *
     * @param array $userValues
     *
     * @return bool
     */
    public function isGrantedToCreate(array $userValues): bool
    {
        if (!$this->loggedInUserId) {
            $this->logger->error(
                'loggedInUserId not set while authorization check isGrantedToCreate: '
                . json_encode($userValues, JSON_PARTIAL_OUTPUT_ON_ERROR)
            );

            return false;
        }
        $authenticatedUserRoleHierarchy = $this->userRoleFinderRepository->getRoleHierarchyByUserId(
            $this->loggedInUserId
        );
        // Returns array with role name as key and hierarchy as value ['role_name' => hierarchy_int]
        // * Lower hierarchy number means higher privileged role
        $userRoleHierarchies = $this->userRoleFinderRepository->getUserRolesHierarchies();

        // Newcomer and advisor are not allowed to do anything from other users - only user edit his own profile
        // Managing advisor may change users
        if ($authenticatedUserRoleHierarchy <= $userRoleHierarchies[UserRole::MANAGING_ADVISOR->value]) {
            // Managing advisors can do everything with users except setting a role higher than advisor
            if ($this->userRoleIsGranted(
                $userValues['user_role_id'] ?? null,
                null,
                $authenticatedUserRoleHierarchy,
                $userRoleHierarchies
            ) === true
            ) {
                return true;
            }

            // If the user role of the user managing advisors or higher wants to change is empty, allowed
            // It's the validation's job to check if the value is valid
            if ($userValues['user_role_id'] === null) {
                return true;
            }
        }
        // There is no need to check if user wants to create his own user as he can't be logged in if the user doesn't exist

        $this->logger->notice(
            'User ' . $this->loggedInUserId . ' tried to create user but isn\'t allowed.'
        );

        return false;
    }

    /**
     * Check if the authenticated user is allowed to assign a user role.
     *
     * @param string|int|null $newUserRoleId (New) user role id to be assigned. Nullable as admins are authorized to
     * set any role, validation should check if the value is valid.
     * @param string|int|null $userRoleIdOfUserToMutate (Existing) user role of user to be changed
     * @param int|null $authenticatedUserRoleHierarchy optional so that it can be called outside this class
     * @param array|null $userRoleHierarchies optional so that it can be called outside this class
     *
     * @return bool
     */
    public function userRoleIsGranted(
        string|int|null $newUserRoleId,
        string|int|null $userRoleIdOfUserToMutate,
        ?int $authenticatedUserRoleHierarchy = null,
        ?array $userRoleHierarchies = null,
    ): bool {
        if (!$this->loggedInUserId) {
            $this->logger->error(
                'loggedInUserId not set while authorization check that user role is granted $userRoleIdOfUserToMutate: '
                . $userRoleIdOfUserToMutate
            );

            return false;
        }
        // $authenticatedUserRoleData and $userRoleHierarchies passed as arguments if called inside this class
        if ($authenticatedUserRoleHierarchy === null) {
            $authenticatedUserRoleHierarchy = $this->userRoleFinderRepository->getRoleHierarchyByUserId(
                $this->loggedInUserId
            );
        }
        if ($userRoleHierarchies === null) {
            // Returns array with role name as key and hierarchy as value ['role_name' => hierarchy_int]
            // * Lower hierarchy number means higher privileged role
            $userRoleHierarchies = $this->userRoleFinderRepository->getUserRolesHierarchies();
        }

        $userRoleHierarchiesById = $this->userRoleFinderRepository->getUserRolesHierarchies(true);

        // Role higher (lower hierarchy number) than managing advisor may assign any role (admin)
        if ($authenticatedUserRoleHierarchy < $userRoleHierarchies[UserRole::MANAGING_ADVISOR->value]) {
            return true;
        }

        if (// Managing advisor can only attribute roles with lower or equal privilege than advisor
            !empty($newUserRoleId)
            && $authenticatedUserRoleHierarchy <= $userRoleHierarchies[UserRole::MANAGING_ADVISOR->value]
            && $userRoleHierarchiesById[$newUserRoleId] >= $userRoleHierarchies[UserRole::ADVISOR->value]
            // And managing advisor may only change advisors or newcomers
            && ($userRoleIdOfUserToMutate === null
                || $userRoleHierarchiesById[$userRoleIdOfUserToMutate] >=
                $userRoleHierarchies[UserRole::ADVISOR->value])
        ) {
            return true;
        }

        return false;
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
        if (!$this->loggedInUserId) {
            $this->logger->error(
                'loggedInUserId not while user update authorization check' .
                json_encode($userDataToUpdate, JSON_PARTIAL_OUTPUT_ON_ERROR)
            );

            return false;
        }
        $grantedUpdateKeys = [];

        $authenticatedUserRoleHierarchy = $this->userRoleFinderRepository->getRoleHierarchyByUserId(
            $this->loggedInUserId
        );
        $userToUpdateRoleData = $this->userRoleFinderRepository->getUserRoleDataFromUser((int)$userIdToUpdate);
        // Returns array with role name as key and hierarchy as value ['role_name' => hierarchy_int]
        // * Lower hierarchy number means higher privileged role
        $userRoleHierarchies = $this->userRoleFinderRepository->getUserRolesHierarchies();

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
            $grantedUpdateKeys[] = 'surname';
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
                    && $this->userRoleIsGranted(
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
                // the authenticated managing_advisor / admin password is asked instead of the old user password
                // but this is too much for this project.
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

    /**
     * Check if authenticated user is allowed to delete user.
     *
     * @param int $userIdToDelete
     * @param bool $log log if forbidden (expected false when function is called for privilege setting)
     *
     * @return bool
     */
    public function isGrantedToDelete(
        int $userIdToDelete,
        bool $log = true
    ): bool {
        if (!$this->loggedInUserId) {
            $this->logger->error(
                'loggedInUserId not set while authorization check isGrantedToDelete $userIdToDelete: '
                . $userIdToDelete
            );

            return false;
        }
        $authenticatedUserRoleHierarchy = $this->userRoleFinderRepository->getRoleHierarchyByUserId(
            $this->loggedInUserId
        );
        $userToDeleteRoleHierarchy = $this->userRoleFinderRepository->getRoleHierarchyByUserId($userIdToDelete);

        // Returns array with role name as key and hierarchy as value ['role_name' => hierarchy_int]
        // * Lower hierarchy number means higher privileged role
        $userRoleHierarchies = $this->userRoleFinderRepository->getUserRolesHierarchies();

        // Only managing_advisor and higher are allowed to delete user and only if the user is advisor or lower or their own
        if (($authenticatedUserRoleHierarchy <= $userRoleHierarchies[UserRole::MANAGING_ADVISOR->value]
                && ($userToDeleteRoleHierarchy >= $userRoleHierarchies[UserRole::ADVISOR->value]
                    || $userIdToDelete === $this->loggedInUserId))
            // or authenticated user is admin
            || $authenticatedUserRoleHierarchy <= $userRoleHierarchies[UserRole::ADMIN->value]) {
            return true;
        }

        if ($log === true) {
            $this->logger->notice(
                'User ' . $this->loggedInUserId . ' tried to delete user but isn\'t allowed.'
            );
        }

        return false;
    }

    /**
     * Check if authenticated user is allowed to read user.
     *
     * @param int|null $userIdToRead null when check for all users
     * @param bool $log log if forbidden (expected false when function is called for privilege setting)
     *
     * @return bool
     */
    public function isGrantedToRead(?int $userIdToRead = null, bool $log = true): bool
    {
        if (!$this->loggedInUserId) {
            $this->logger->error(
                'loggedInUserId not set while authorization check isGrantedToRead $userIdToRead: '
                . $userIdToRead
            );

            return false;
        }
        $authenticatedUserRoleHierarchy = $this->userRoleFinderRepository->getRoleHierarchyByUserId(
            $this->loggedInUserId
        );
        // Returns array with role name as key and hierarchy as value ['role_name' => hierarchy_int]
        // * Lower hierarchy number means higher privileged role
        $userRoleHierarchies = $this->userRoleFinderRepository->getUserRolesHierarchies();

        // Only managing advisor and higher privileged are allowed to see other users
        // If the user role hierarchy of the authenticated user is lower or equal
        // than the one from the managing advisor -> authorized
        if ($authenticatedUserRoleHierarchy <= $userRoleHierarchies[UserRole::MANAGING_ADVISOR->value]
            // or user wants to view his own profile in which case also -> authorized
            || $this->loggedInUserId === $userIdToRead) {
            return true;
        }

        if ($log === true) {
            $this->logger->notice('User ' . $this->loggedInUserId . ' tried to read user but isn\'t allowed.');
        }

        return false;
    }

    /**
     * Check if the authenticated user is allowed to read user activity.
     *
     * @param int $userIdToRead
     * @param bool $log log if forbidden
     *
     * @return bool
     */
    public function isGrantedToReadUserActivity(
        int $userIdToRead,
        bool $log = true
    ): bool {
        if (!$this->loggedInUserId) {
            $this->logger->error(
                'loggedInUserId not set while authorization check isGrantedToReadUserActivity $userIdToRead: '
                . $userIdToRead
            );

            return false;
        }

        $authenticatedUserRoleHierarchy = $this->userRoleFinderRepository->getRoleHierarchyByUserId(
            $this->loggedInUserId
        );
        // Returns array with role name as key and hierarchy as value ['role_name' => hierarchy_int]
        // * Lower hierarchy number means higher privileged role
        $userRoleHierarchies = $this->userRoleFinderRepository->getUserRolesHierarchies();

        $userToReadRoleHierarchy = $this->userRoleFinderRepository->getRoleHierarchyByUserId($userIdToRead);

        // Only managing advisors are allowed to see user activity, but only if target user role is not higher than also managing advisor
        if (($authenticatedUserRoleHierarchy <= $userRoleHierarchies[UserRole::MANAGING_ADVISOR->value]
                && $userToReadRoleHierarchy >= $userRoleHierarchies[UserRole::MANAGING_ADVISOR->value])
            // or authenticated user is admin
            || $authenticatedUserRoleHierarchy <= $userRoleHierarchies[UserRole::ADMIN->value]
            // or user wants to view his own activity
            || $this->loggedInUserId === $userIdToRead) {
            return true;
        }

        if ($log === true) {
            $this->logger->notice(
                "User $this->loggedInUserId tried to read activity of user $userIdToRead but isn't allowed."
            );
        }

        return false;
    }
}
