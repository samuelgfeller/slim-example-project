<?php

namespace App\Domain\Client\Service\Authorization;

use App\Application\Data\UserNetworkSessionData;
use App\Domain\Authentication\Repository\UserRoleFinderRepository;
use App\Domain\Client\Data\ClientData;
use App\Domain\Client\Data\ClientReadResult;
use App\Domain\Note\Service\Authorization\NotePermissionVerifier;
use App\Domain\User\Enum\UserRole;
use Psr\Log\LoggerInterface;

/**
 * Check if authenticated user is permitted to do actions
 * Roles: newcomer < advisor < managing_advisor < administrator.
 */
class ClientPermissionVerifier
{
    private ?int $loggedInUserId = null;

    public function __construct(
        private readonly UserRoleFinderRepository $userRoleFinderRepository,
        private readonly UserNetworkSessionData $userNetworkSessionData,
        private readonly LoggerInterface $logger,
    ) {
        $this->loggedInUserId = $this->userNetworkSessionData->userId;
    }

    /**
     * Check if authenticated user is allowed to create client.
     *
     * @param ClientData|null $client null if check before actual client creation
     *  request otherwise it has to be provided
     *
     * @return bool
     */
    public function isGrantedToCreate(?ClientData $client = null): bool
    {
        if (!$this->loggedInUserId) {
            $this->logger->error(
                'loggedInUserId not set while isGrantedToCreate authorization check $client: '
                . json_encode($client, JSON_PARTIAL_OUTPUT_ON_ERROR)
            );
            return false;
        }
        $authenticatedUserRoleHierarchy = $this->userRoleFinderRepository->getRoleHierarchyByUserId(
            $this->loggedInUserId
        );
        // Returns array with role name as key and hierarchy as value [role_name => hierarchy_int]
        // * Lower hierarchy number means higher privileged role
        $userRoleHierarchies = $this->userRoleFinderRepository->getUserRolesHierarchies();

        // Newcomer is not allowed to do anything
        // If hierarchy number is greater or equals newcomer it means that user is not allowed
        if ($authenticatedUserRoleHierarchy <= $userRoleHierarchies[UserRole::ADVISOR->value]) {
            // Advisor may create clients but can only assign them to himself or leave it unassigned.
            // If $client is null (not provided), advisor is authorized (being used to check if create btn should
            // be displayed in template)
            if ($client === null || $this->isGrantedToAssignUserToClient($client->userId)) {
                // If authenticated user is at least advisor and client user id is himself, or it's a
                // managing_advisor (logic in isGrantedToAssignUserToClient) -> granted to create
                return true;
            }
        }

        $this->logger->notice('User ' . $this->loggedInUserId . ' tried to create client but isn\'t allowed.');

        return false;
    }

    /**
     * Check if the authenticated user is allowed to assign user to client.
     * (Client id not needed as the same rules applies for new clients and all existing clients)
     * In own function to be used to filter dropdown options for frontend.
     *
     * @param int|null $assignedUserId
     * @param int|null $authenticatedUserRoleHierarchy optional so that it can be called outside this class
     * @param array|null $userRoleHierarchies optional so that it can be called outside this class
     *
     * @return bool|void
     */
    public function isGrantedToAssignUserToClient(
        ?int $assignedUserId,
        ?int $authenticatedUserRoleHierarchy = null,
        ?array $userRoleHierarchies = null
    ) {
        if (!$this->loggedInUserId) {
            $this->logger->error(
                'loggedInUserId not set while isGrantedToAssignUserToClient authorization check $assignedUserId: '
                . $assignedUserId
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
            // Returns array with role name as key and hierarchy as value [role_name => hierarchy_int]
            // * Lower hierarchy number means higher privileged role
            $userRoleHierarchies = $this->userRoleFinderRepository->getUserRolesHierarchies();
        }

        // If hierarchy privilege is greater or equals advisor, it means that user may create the client
        if ($authenticatedUserRoleHierarchy <= $userRoleHierarchies[UserRole::ADVISOR->value]) {
            // Advisor may create clients but can only assign them to himself or leave it unassigned
            if ($assignedUserId === $this->loggedInUserId || $assignedUserId === null
                // managing advisor can link user to someone else
                || $authenticatedUserRoleHierarchy <= $userRoleHierarchies[UserRole::MANAGING_ADVISOR->value]) {
                // If authenticated user is at least advisor and client user id is authenticated user himself,
                // null (unassigned) or authenticated user is managing_advisor -> granted to assign
                return true;
            }
        }
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
        if (!$this->loggedInUserId) {
            $this->logger->error(
                'loggedInUserId not set while isGrantedToUpdate authorization check $clientDataToUpdate: '
                . json_encode($clientDataToUpdate, JSON_PARTIAL_OUTPUT_ON_ERROR)
            );
            return false;
        }
        $authenticatedUserRoleHierarchy = $this->userRoleFinderRepository->getRoleHierarchyByUserId(
            $this->loggedInUserId
        );
        // Returns array with role name as key and hierarchy as value [role_name => hierarchy_int]
        // * Lower hierarchy number means higher privileged role
        $userRoleHierarchies = $this->userRoleFinderRepository->getUserRolesHierarchies();

        // Roles: newcomer < advisor < managing_advisor < administrator
        // If logged-in hierarchy value is smaller or equal advisor -> granted
        if ($authenticatedUserRoleHierarchy <= $userRoleHierarchies['advisor']) {
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

            /** Update main note authorization is in @see NotePermissionVerifier::isGrantedToUpdate () */

            // Everything that owner and managing_advisor is permitted to do
            // advisor may only edit client_status_id if he's owner | managing_advisor and higher is allowed
            if ($this->loggedInUserId === $ownerId
                || $authenticatedUserRoleHierarchy <= $userRoleHierarchies['managing_advisor']) {
                // Check if client_status_id is among data to be changed if yes add it to $grantedUpdateKeys array
                if (array_key_exists('client_status_id', $clientDataToUpdate)) {
                    $grantedUpdateKeys[] = 'client_status_id';
                }
            }
            // Things that only managing_advisor and higher privileged are allowed to do
            if ($authenticatedUserRoleHierarchy <= $userRoleHierarchies['managing_advisor']
                // isGrantedToAssignUserToClient CANNOT be used as it expects a real user_id which is not provided
                // in the case where user_id value is the string "value" from (ClientAuthGetter) to check if
                // the authenticated user is allowed to change assigned user (html <select> enabled/disabled)
                && array_key_exists('user_id', $clientDataToUpdate)) {
                $grantedUpdateKeys[] = 'user_id';
            }

            // If there is a request to undelete coming from the client,
            // the same authorization rules than deletion are valid
            if (array_key_exists('deleted_at', $clientDataToUpdate) && $this->isGrantedToDelete($ownerId, $log)) {
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

    /**
     * Check if the authenticated user is allowed to delete client.
     *
     * @param int|null $ownerId
     * @param bool $log log if forbidden (expected false when function is called for privilege setting)
     *
     * @return bool
     */
    public function isGrantedToDelete(?int $ownerId, bool $log = true): bool
    {
        if (!$this->loggedInUserId) {
            $this->logger->error('loggedInUserId not set while isGrantedToDelete authorization check');
            return false;
        }
        $authenticatedUserRoleHierarchy = $this->userRoleFinderRepository->getRoleHierarchyByUserId(
            $this->loggedInUserId
        );
        // Returns array with role name as key and hierarchy as value [role_name => hierarchy_int]
        // * Lower hierarchy number means higher privileged role
        $userRoleHierarchies = $this->userRoleFinderRepository->getUserRolesHierarchies();

        // Only managing_advisor and higher are allowed to delete client so user has to at least have this role
        if ($authenticatedUserRoleHierarchy <= $userRoleHierarchies['managing_advisor']) {
            return true;
        }

        if ($log === true) {
            $this->logger->notice(
                'User ' . $this->loggedInUserId . ' tried to delete client but isn\'t allowed.'
            );
        }

        return false;
    }

    /**
     * Instead of a isGrantedToListClient(), this function checks
     * with isGrantedToReadClient and removes clients that
     * authenticated user may not see.
     *
     * @param ClientReadResult[]|null $clients
     *
     * @return ClientReadResult[]
     */
    public function removeNonAuthorizedClientsFromList(?array $clients): array
    {
        $authorizedClients = [];
        foreach ($clients ?? [] as $client) {
            // Check if the authenticated user is allowed to read each client and if yes, add it to the return array
            if ($this->isGrantedToRead($client->userId)) {
                $authorizedClients[] = $client;
            }
        }

        return $authorizedClients;
    }

    /**
     * Check if the authenticated user is allowed to read client.
     *
     * @param int|null $ownerId
     * @param string|\DateTimeImmutable|null $deletedAt
     * @param bool $log log if forbidden (expected false when function is called for privilege setting)
     *
     * @return bool
     */
    public function isGrantedToRead(
        ?int $ownerId,
        string|\DateTimeImmutable|null $deletedAt = null,
        bool $log = true
    ): bool {
        if ($this->loggedInUserId) {
            $authenticatedUserRoleHierarchy = $this->userRoleFinderRepository->getRoleHierarchyByUserId(
                $this->loggedInUserId
            );
            // Returns array with role name as key and hierarchy as value [role_name => hierarchy_int]
            // * Lower hierarchy number means higher privileged role
            $userRoleHierarchies = $this->userRoleFinderRepository->getUserRolesHierarchies();

            // Newcomer are allowed to see all clients regardless of owner if not deleted
            if ($authenticatedUserRoleHierarchy <= $userRoleHierarchies[UserRole::NEWCOMER->value]
                && $deletedAt === null
            ) {
                return true;
            }
            // Managing advisors can see all clients including deleted ones
            if ($authenticatedUserRoleHierarchy <= $userRoleHierarchies[UserRole::MANAGING_ADVISOR->value]) {
                return true;
            }
        }
        if ($log === true) {
            $this->logger->notice(
                'User ' . $this->loggedInUserId . ' tried to read client but isn\'t allowed.'
            );
        }

        return false;
    }
}
