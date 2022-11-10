<?php

namespace App\Domain\User\Authorization;

use App\Domain\Factory\LoggerFactory;
use App\Domain\User\Data\UserData;
use App\Domain\User\Enum\UserRole;
use App\Infrastructure\Authentication\UserRoleFinderRepository;
use Odan\Session\SessionInterface;
use Psr\Log\LoggerInterface;

/**
 * Check if authenticated user is permitted to do actions
 * Roles: newcomer < advisor < managing_advisor < administrator
 */
class UserAuthorizationChecker
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly UserRoleFinderRepository $userRoleFinderRepository,
        private readonly SessionInterface $session,
        LoggerFactory $loggerFactory
    ) {
        $this->logger = $loggerFactory->addFileHandler('error.log')->createInstance('user-authorization');
    }

    /**
     * Check if authenticated user is allowed to create
     * Important to have user role in the object
     *
     * @param UserData $userData
     * @return bool
     */
    public function isGrantedToCreate(UserData $userData): bool
    {
        if (($loggedInUserId = (int)$this->session->get('user_id')) !== 0) {
            $authenticatedUserRoleData = $this->userRoleFinderRepository->getUserRoleDataFromUser(
                $loggedInUserId
            );
            /** @var array{role_name: int} $userRoleHierarchies lower hierarchy number means higher privilege */
            $userRoleHierarchies = $this->userRoleFinderRepository->getUserRolesHierarchies();

            // Newcomer is not allowed to do anything
            // Only user editing his own profile or managing advisor may change users
            if ($authenticatedUserRoleData->hierarchy <= $userRoleHierarchies[UserRole::MANAGING_ADVISOR->value]) {
                $userRoleHierarchiesById = $this->userRoleFinderRepository->getUserRolesHierarchies(true);
                // Managing advisors can do everything with users except the role
                if ($userData->user_role_id !== null &&
                    // The user role can't be set higher than advisor
                    $userRoleHierarchiesById[$userData->user_role_id] >= $userRoleHierarchies[UserRole::ADVISOR->value]) {
                    return true;
                }
            }
            // There is no need to check if user want to create his own user as he can't be logged in if the user doesn't exist
        }

        $this->logger->notice(
            'User ' . $loggedInUserId . ' tried to create user but isn\'t allowed.'
        );
        return false;
    }

    /**
     * Logic to check if logged-in user is granted to update user
     *
     * @param array $userDataToUpdate validated array with as key the column to
     * update and value the new value. There may be one or multiple entries,
     * depending on what the user wants to update
     *
     * @param string|int $userIdToUpdate
     * @param bool $log log if forbidden (expected false when function is called for privilege setting)
     * @return bool
     */
    public function isGrantedToUpdate(array $userDataToUpdate, string|int $userIdToUpdate, bool $log = true): bool
    {
        $grantedUpdateKeys = [];
        if (($loggedInUserId = (int)$this->session->get('user_id')) !== 0) {
            $authenticatedUserRoleData = $this->userRoleFinderRepository->getUserRoleDataFromUser(
                $loggedInUserId
            );
            $userToUpdateRoleData = $this->userRoleFinderRepository->getUserRoleDataFromUser(
                (int)$userIdToUpdate
            );
            /** @var array{role_name: int} $userRoleHierarchies role name as key and hierarchy value
             * lower hierarchy number means higher privilege */
            $userRoleHierarchies = $this->userRoleFinderRepository->getUserRolesHierarchies();
            $userRoleHierarchiesById = $this->userRoleFinderRepository->getUserRolesHierarchies(true);

            // Roles: newcomer < advisor < managing_advisor < administrator
            // If logged-in hierarchy value is smaller or equal managing advisor
            if ((($authenticatedUserRoleData->hierarchy <= $userRoleHierarchies[UserRole::MANAGING_ADVISOR->value]
                        // and that the user to change is has no role higher than advisor
                        && $userToUpdateRoleData->hierarchy >= $userRoleHierarchies[UserRole::ADVISOR->value])
                    // or it's an admin which is allowed to change users with role
                    || $authenticatedUserRoleData->hierarchy <= $userRoleHierarchies[UserRole::ADMIN->value])
                ||// or user edits his own profile
                $loggedInUserId === (int)$userIdToUpdate
            ) {
                // Managing advisor cannot change other managing advisors or admins but admins can change themselves and everyone else

                // Things that managing advisor and owner user are allowed to change
                if (array_key_exists('general_data', $userDataToUpdate)) {
                    // General data is the "main" data like first name, last name and email
                    $grantedUpdateKeys[] = 'general_data';
                }
                if (array_key_exists('first_name', $userDataToUpdate)) {
                    $grantedUpdateKeys[] = 'first_name';
                }
                if (array_key_exists('surname', $userDataToUpdate)) {
                    $grantedUpdateKeys[] = 'surname';
                }
                if (array_key_exists('email', $userDataToUpdate)) {
                    $grantedUpdateKeys[] = 'email';
                }
                if (array_key_exists('password_hash', $userDataToUpdate)) {
                    $grantedUpdateKeys[] = 'password_hash';
                }

                // Things that only managing_advisor and higher privileged are allowed to do with users that are
                // If user is managing advisor we know by the parent if-statement that the user to change has not higher
                // role than advisor
                if ($authenticatedUserRoleData->hierarchy <= $userRoleHierarchies[UserRole::MANAGING_ADVISOR->value]) {
                    if (array_key_exists('status', $userDataToUpdate)) {
                        $grantedUpdateKeys[] = 'status';
                    }
                    if (array_key_exists('user_role_id', $userDataToUpdate)) {
                        // If authenticated user is admin it means that any user role is permitted
                        if ($authenticatedUserRoleData->hierarchy <= $userRoleHierarchies[UserRole::ADMIN->value]) {
                            $grantedUpdateKeys[] = 'user_role_id';
                        } // Managing advisor can only attribute roles with lower or equal privilege than advisor
                        elseif ($userRoleHierarchiesById[$userDataToUpdate['user_role_id']] >=
                            $userRoleHierarchies[UserRole::ADVISOR->value]) {
                            $grantedUpdateKeys[] = 'user_role_id';
                        }
                    }
                }
                // Owner user (profile edit) is not allowed to change its user role or status
            }
        }

        // If data that the user wanted to update and the grantedUpdateKeys are equal by having the same keys -> granted
        foreach ($userDataToUpdate as $key => $value) {
            // If at least one array key doesn't exist in $grantedUpdateKeys it means that user is not permitted
            if (!in_array($key, $grantedUpdateKeys, true)) {
                if ($log === true) {
                    $this->logger->notice(
                        'User ' . $loggedInUserId . ' tried to update user but isn\'t allowed to change' .
                        $key . ' to "' . $value . '".'
                    );
                }
                return false;
            }
        }
        return true;
    }

    /**
     * Check if authenticated user is allowed to delete user
     *
     * @param int $userIdToDelete
     * @param bool $log log if forbidden (expected false when function is called for privilege setting)
     * @return bool
     */
    public function isGrantedToDelete(int $userIdToDelete, bool $log = true): bool
    {
        if (($loggedInUserId = (int)$this->session->get('user_id')) !== 0) {
            $authenticatedUserRoleData = $this->userRoleFinderRepository->getUserRoleDataFromUser(
                $loggedInUserId
            );
            $userToDeleteRoleData = $this->userRoleFinderRepository->getUserRoleDataFromUser($userIdToDelete);

            /** @var array{role_name: int} $userRoleHierarchies lower hierarchy number means higher privilege */
            $userRoleHierarchies = $this->userRoleFinderRepository->getUserRolesHierarchies();

            // Only managing_advisor and higher are allowed to delete user and only if the user is advisor or lower
            if (($authenticatedUserRoleData->hierarchy <= $userRoleHierarchies[UserRole::MANAGING_ADVISOR->value]
                    && $userToDeleteRoleData->hierarchy >= $userRoleHierarchies[UserRole::ADVISOR->value])
                // or authenticated user is admin
                || $authenticatedUserRoleData->hierarchy <= $userRoleHierarchies[UserRole::ADMIN->value]) {
                return true;
            }
        }

        if ($log === true) {
            $this->logger->notice(
                'User ' . $loggedInUserId . ' tried to delete user but isn\'t allowed.'
            );
        }
        return false;
    }

    /**
     * Check if authenticated user is allowed to read user
     *
     * @param int $userIdToRead
     * @param bool $log log if forbidden (expected false when function is called for privilege setting)
     * @return bool
     */
    public function isGrantedToRead(int $userIdToRead, bool $log = true): bool
    {
        if (($loggedInUserId = (int)$this->session->get('user_id')) !== 0) {
            $authenticatedUserRoleData = $this->userRoleFinderRepository->getUserRoleDataFromUser(
                $loggedInUserId
            );
            /** @var array{role_name: int} $userRoleHierarchies lower hierarchy number means higher privilege */
            $userRoleHierarchies = $this->userRoleFinderRepository->getUserRolesHierarchies();

            // Only managing advisor and higher privilege are allowed to see other users
            if ($authenticatedUserRoleData->hierarchy <= $userRoleHierarchies[UserRole::MANAGING_ADVISOR->value]
                // or user wants to view his own profile
                || $loggedInUserId === $userIdToRead) {
                return true;
            }
        }

        if ($log === true) {
            $this->logger->notice(
                'User ' . $loggedInUserId . ' tried to read user but isn\'t allowed.'
            );
        }
        return false;
    }
}