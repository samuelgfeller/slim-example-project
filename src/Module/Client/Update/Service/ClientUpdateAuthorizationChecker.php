<?php

namespace App\Module\Client\Update\Service;

use App\Application\Data\UserNetworkSessionData;
use App\Module\Authorization\Repository\AuthorizationUserRoleFinderRepository;
use App\Module\Client\AssignUser\Service\ClientAssignUserAuthorizationChecker;
use App\Module\Client\Delete\Service\ClientDeleteAuthorizationChecker;
use App\Module\Note\Authorization\NotePermissionVerifier;
use App\Module\Note\Update\Service\NoteUpdateAuthorizationChecker;
use App\Module\User\Enum\UserRole;
use Psr\Log\LoggerInterface;

/**
 * Check if the authenticated user is permitted to do actions.
 * Roles: newcomer < advisor < managing_advisor < administrator.
 */
final class ClientUpdateAuthorizationChecker
{
    private ?int $loggedInUserId = null;

    public function __construct(
        private readonly AuthorizationUserRoleFinderRepository $authorizationUserRoleFinderRepository,
        private readonly ClientAssignUserAuthorizationChecker $clientAssignUserAuthorizationChecker,
        private readonly ClientDeleteAuthorizationChecker $clientDeleteAuthorizationChecker,
        private readonly UserNetworkSessionData $userNetworkSessionData,
        private readonly LoggerInterface $logger,
    ) {
        $this->loggedInUserId = $this->userNetworkSessionData->userId;
    }

    /**
     * Logic to check if authenticated user is granted to update client.
     *
     * @param array $clientDataToUpdate validated array with as key the column to
     * update and value the new value (or fictive "value").
     * There may be one or multiple entries, depending on what the user wants to update.
     * @param int|null $ownerId user_id linked to client
     * @param bool $log log if forbidden (expected false when function is called for privilege setting)
     *
     * @return bool
     */
    public function isGrantedToUpdate(array $clientDataToUpdate, ?int $ownerId, bool $log = true): bool
    {
        $grantedUpdateKeys = [];
        if ($this->loggedInUserId === null) {
            $this->logger->error(
                'loggedInUserId not set while isGrantedToUpdate authorization check $clientDataToUpdate: '
                . json_encode($clientDataToUpdate, JSON_PARTIAL_OUTPUT_ON_ERROR)
            );

            return false;
        }
        $authenticatedUserRoleHierarchy = $this->authorizationUserRoleFinderRepository->getRoleHierarchyByUserId(
            $this->loggedInUserId
        );
        // Returns array with role name as key and hierarchy as value ['role_name' => hierarchy_int]
        // * Lower hierarchy number means higher privileged role
        $userRoleHierarchies = $this->authorizationUserRoleFinderRepository->getUserRolesHierarchies();

        // Roles: newcomer < advisor < managing_advisor < administrator
        // If logged-in hierarchy value is smaller or equal advisor -> granted
        if ($authenticatedUserRoleHierarchy <= $userRoleHierarchies[UserRole::ADVISOR->value]) {
            // Things that advisor is allowed to change for all client records even when not owner
            // "personal_info" is the same as the group of columns that follows it
            $grantedUpdateKeys[] = 'personal_info';
            // Same as personal info but in separate columns to be returned as granted keys
            $grantedUpdateKeys[] = 'first_name';
            $grantedUpdateKeys[] = 'last_name';
            $grantedUpdateKeys[] = 'birthdate';
            $grantedUpdateKeys[] = 'location';
            $grantedUpdateKeys[] = 'phone';
            $grantedUpdateKeys[] = 'email';
            $grantedUpdateKeys[] = 'sex';
            $grantedUpdateKeys[] = 'vigilance_level';

            /** Update main note authorization is in @see NoteUpdateAuthorizationChecker::isGrantedToUpdate () */

            // Everything that owner and managing_advisor is permitted to do
            // advisor may only edit client_status_id if they are owner | managing_advisor and higher is allowed
            if ($this->loggedInUserId === $ownerId
                || $authenticatedUserRoleHierarchy <= $userRoleHierarchies[UserRole::MANAGING_ADVISOR->value]) {
                // Check if client_status_id is among data to be changed if yes add it to $grantedUpdateKeys array
                if (array_key_exists('client_status_id', $clientDataToUpdate)) {
                    $grantedUpdateKeys[] = 'client_status_id';
                }
            }

            // Assign user to client
            if (array_key_exists('user_id', $clientDataToUpdate) && (
                $this->clientAssignUserAuthorizationChecker->isGrantedToAssignUserToClient(
                    $clientDataToUpdate['user_id'],
                    $authenticatedUserRoleHierarchy,
                    $userRoleHierarchies
                ))
                //         // Advisors (already checked above) may only assign clients to themselves or unassign themselves
                //         ($this->loggedInUserId === $clientDataToUpdate['user_id'] || $clientDataToUpdate['user_id'] === null)
                //         // Managing_advisor and higher may assign clients to any advisor
                //         || ($authenticatedUserRoleHierarchy <= $userRoleHierarchies[UserRole::MANAGING_ADVISOR->value])
                //     )

                // isGrantedToAssignUserToClient CANNOT be used as it expects a real user_id which is not provided
                // in the case where user_id value is the string "value" from (ClientPrivilegeDeterminer) to check if
                // the authenticated user is allowed to change the assigned user. (To enable/disable html <select>)

            ) {
                $grantedUpdateKeys[] = 'user_id';
            }

            // If there is a request to undelete coming from the client,
            // the same authorization rules than deletion are valid
            if (array_key_exists('deleted_at', $clientDataToUpdate)
                && $this->clientDeleteAuthorizationChecker->isGrantedToDelete($ownerId, $log)) {
                $grantedUpdateKeys[] = 'deleted_at';
            }
        }
        // If data that the user wanted to update and the grantedUpdateKeys are equal by having the same keys -> granted
        foreach ($clientDataToUpdate as $key => $value) {
            // If at least one array key doesn't exist in $grantedUpdateKeys it means that user is not permitted
            if (!in_array($key, $grantedUpdateKeys, true)) {
                if ($log === true) {
                    $this->logger->notice(
                        'User ' . $this->loggedInUserId . ' tried to update client but isn\'t allowed to change' .
                        $key . ' to "' . $value . '".'
                    );
                }

                return false;
            }
        }

        return true;
    }
}
