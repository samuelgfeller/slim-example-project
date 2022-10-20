<?php

namespace App\Domain\Client\Authorization;

use App\Infrastructure\Authentication\UserRoleFinderRepository;
use Odan\Session\SessionInterface;

class ClientAuthorizationChecker
{

    public function __construct(
        private readonly UserRoleFinderRepository $userRoleFinderRepository,
        private readonly SessionInterface $session,
    )
    {
    }

    /**
     * Logic to check if logged-in user is granted to update client
     *
     * @param array $clientDataToUpdate validated array with as key the column to
     * update and value the new value. There may be one or multiple entries,
     * depending on what the user wants to update
     *
     * @param int $ownerId user_id linked to client
     *
     * @return bool
     */
    public function isGrantedToUpdateClient(array $clientDataToUpdate, int $ownerId): bool
    {
        $grantedUpdateKeys = [];
        if (($loggedInUserId = $this->session->get('user_id')) !== null) {
            $authenticatedUserRoleData = $this->userRoleFinderRepository->getUserRoleDataFromUser($loggedInUserId);
            /** @var array{role_name: int} $userRoleHierarchies role name as key and hierarchy value
             * (lower hierarchy number means higher privilege) */
            $userRoleHierarchies = $this->userRoleFinderRepository->getUserRolesHierarchies();

            // Roles: newcomer < advisor < managing_advisor < administrator
            // If logged-in hierarchy value is smaller or equal advisor -> granted
            if ($authenticatedUserRoleData->hierarchy <= $userRoleHierarchies['advisor']) {
                // Things that advisor is allowed to change for all client records even when not owner
                if (array_key_exists('first_name', $clientDataToUpdate)) {
                    $grantedUpdateKeys[] = 'first_name';
                }
                if (array_key_exists('last_name', $clientDataToUpdate)) {
                    $grantedUpdateKeys[] = 'last_name';
                }

                // Everything that owner and managing_advisor is permitted to do
                // advisor may only edit client_status_id if he's owner | managing_advisor and higher is allowed
                if ($loggedInUserId === $ownerId || $authenticatedUserRoleData->hierarchy <= $userRoleHierarchies['managing_advisor']) {
                    // Check if client_status_id is among data to be changed if yes add it to $grantedUpdateKeys array
                    if (array_key_exists('client_status_id', $clientDataToUpdate)) {
                        $grantedUpdateKeys[] = 'client_status_id';
                    }
                }
                // Things that only managing_advisor and higher privileged are allowed to do
                if ($authenticatedUserRoleData->hierarchy <= $userRoleHierarchies['managing_advisor']) {
                    if (array_key_exists('user_id', $clientDataToUpdate)) {
                        $grantedUpdateKeys[] = 'user_id';
                    }
                }
            }
        }
        // If data that the user wanted to update and the grantedUpdateKeys are equal by having the same keys -> granted
        foreach ($clientDataToUpdate as $key => $value) {
            // If at least one array key doesn't exist in $grantedUpdateKeys it means that user is not permitted
            if (!in_array($key, $grantedUpdateKeys, true)) {
                return false;
            }
        }
        return true;
    }
}