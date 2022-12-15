<?php

namespace App\Domain\Client\Authorization;

use App\Domain\Client\Data\ClientData;
use App\Domain\Client\Data\ClientResultData;
use App\Domain\Factory\LoggerFactory;
use App\Domain\User\Enum\UserRole;
use App\Infrastructure\Authentication\UserRoleFinderRepository;
use Odan\Session\SessionInterface;
use Psr\Log\LoggerInterface;

/**
 * Check if authenticated user is permitted to do actions
 * Roles: newcomer < advisor < managing_advisor < administrator.
 */
class ClientAuthorizationChecker
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly UserRoleFinderRepository $userRoleFinderRepository,
        private readonly SessionInterface $session,
        LoggerFactory $loggerFactory
    ) {
        $this->logger = $loggerFactory->addFileHandler('error.log')->createInstance('client-authorization');
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
        if (($loggedInUserId = (int)$this->session->get('user_id')) !== 0) {
            $authenticatedUserRoleData = $this->userRoleFinderRepository->getUserRoleDataFromUser(
                $loggedInUserId
            );
            /** @var array{role_name: int} $userRoleHierarchies lower hierarchy number means higher privilege */
            $userRoleHierarchies = $this->userRoleFinderRepository->getUserRolesHierarchies();

            // Newcomer is not allowed to do anything
            // If hierarchy number is greater or equals newcomer it means that user is not allowed
            if ($authenticatedUserRoleData->hierarchy <= $userRoleHierarchies[UserRole::ADVISOR->value]) {
                // Advisor may create clients but can't assign them to someone other than himself
                // If $client is null (not provided), advisor is authorized (used to check if create btn should be displayed)
                if ($client === null || $client->userId === $loggedInUserId ||
                    // managing advisor can link user to someone else
                    $authenticatedUserRoleData->hierarchy <= $userRoleHierarchies[UserRole::MANAGING_ADVISOR->value]) {
                    // If at least advisor and client user id is authenticated user, or it's a managing_advisor -> granted
                    return true;
                }
            }
        }

        $this->logger->notice(
            'User ' . $loggedInUserId . ' tried to create client but isn\'t allowed.'
        );

        return false;
    }

    /**
     * Logic to check if logged-in user is granted to update client.
     *
     * @param array $clientDataToUpdate validated array with as key the column to
     * update and value the new value. There may be one or multiple entries,
     * depending on what the user wants to update
     * @param int|null $ownerId user_id linked to client
     * @param bool $log log if forbidden (expected false when function is called for privilege setting)
     *
     * @return bool
     */
    public function isGrantedToUpdate(array $clientDataToUpdate, ?int $ownerId, bool $log = true): bool
    {
        $grantedUpdateKeys = [];
        if (($loggedInUserId = (int)$this->session->get('user_id')) !== 0) {
            $authenticatedUserRoleData = $this->userRoleFinderRepository->getUserRoleDataFromUser(
                $loggedInUserId
            );
            /** @var array{role_name: int} $userRoleHierarchies role name as key and hierarchy value
             * (lower hierarchy number means higher privilege) */
            $userRoleHierarchies = $this->userRoleFinderRepository->getUserRolesHierarchies();

            // Roles: newcomer < advisor < managing_advisor < administrator
            // If logged-in hierarchy value is smaller or equal advisor -> granted
            if ($authenticatedUserRoleData->hierarchy <= $userRoleHierarchies['advisor']) {
                // Things that advisor is allowed to change for all client records even when not owner
                // "main_data" is the same as the group of columns that follows it
                if (array_key_exists('main_data', $clientDataToUpdate)) {
                    $grantedUpdateKeys[] = 'main_data';
                }
                // Same as main data but in separate columns to be returned as granted keys
                if (array_key_exists('first_name', $clientDataToUpdate)) {
                    $grantedUpdateKeys[] = 'first_name';
                }
                if (array_key_exists('last_name', $clientDataToUpdate)) {
                    $grantedUpdateKeys[] = 'last_name';
                }
                if (array_key_exists('birthdate', $clientDataToUpdate)) {
                    $grantedUpdateKeys[] = 'birthdate';
                }
                if (array_key_exists('location', $clientDataToUpdate)) {
                    $grantedUpdateKeys[] = 'location';
                }
                if (array_key_exists('phone', $clientDataToUpdate)) {
                    $grantedUpdateKeys[] = 'phone';
                }
                if (array_key_exists('email', $clientDataToUpdate)) {
                    $grantedUpdateKeys[] = 'email';
                }
                if (array_key_exists('sex', $clientDataToUpdate)) {
                    $grantedUpdateKeys[] = 'sex';
                }
                if (array_key_exists('vigilance_level', $clientDataToUpdate)) {
                    $grantedUpdateKeys[] = 'vigilance_level';
                }
                /** Update main note authorization is in @see NoteAuthorizationChecker::isGrantedToUpdate () */

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
                // If there is an undelete request on client, the same authorization rules than deletion are valid
                if (array_key_exists('deleted_at', $clientDataToUpdate) && $this->isGrantedToDelete($ownerId, $log)) {
                    $grantedUpdateKeys[] = 'deleted_at';
                }
            }
        }
        // If data that the user wanted to update and the grantedUpdateKeys are equal by having the same keys -> granted
        foreach ($clientDataToUpdate as $key => $value) {
            // If at least one array key doesn't exist in $grantedUpdateKeys it means that user is not permitted
            if (!in_array($key, $grantedUpdateKeys, true)) {
                if ($log === true) {
                    $this->logger->notice(
                        'User ' . $loggedInUserId . ' tried to update client but isn\'t allowed to change' .
                        $key . ' to "' . $value . '".'
                    );
                }

                return false;
            }
        }

        return true;
    }

    /**
     * Check if authenticated user is allowed to delete client.
     *
     * @param int|null $ownerId
     * @param bool $log log if forbidden (expected false when function is called for privilege setting)
     *
     * @return bool
     */
    public function isGrantedToDelete(?int $ownerId, bool $log = true): bool
    {
        if (($loggedInUserId = (int)$this->session->get('user_id')) !== 0) {
            $authenticatedUserRoleData = $this->userRoleFinderRepository->getUserRoleDataFromUser(
                $loggedInUserId
            );
            /** @var array{role_name: int} $userRoleHierarchies lower hierarchy number means higher privilege */
            $userRoleHierarchies = $this->userRoleFinderRepository->getUserRolesHierarchies();

            // Only managing_advisor and higher are allowed to delete client so user has to at least have this role
            if ($authenticatedUserRoleData->hierarchy <= $userRoleHierarchies['managing_advisor']) {
                return true;
            }
        }
        if ($log === true) {
            $this->logger->notice(
                'User ' . $loggedInUserId . ' tried to delete client but isn\'t allowed.'
            );
        }

        return false;
    }

    /**
     * Check if authenticated user is allowed to read client.
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
        if (($loggedInUserId = (int)$this->session->get('user_id')) !== 0) {
            $authenticatedUserRoleData = $this->userRoleFinderRepository->getUserRoleDataFromUser(
                $loggedInUserId
            );
            /** @var array{role_name: int} $userRoleHierarchies lower hierarchy number means higher privilege */
            $userRoleHierarchies = $this->userRoleFinderRepository->getUserRolesHierarchies();

            // Newcomer are allowed to see all clients regardless of owner if not deleted
            if ($authenticatedUserRoleData->hierarchy <= $userRoleHierarchies[UserRole::NEWCOMER->value]
                && $deletedAt === null
            ) {
                return true;
            }
            // Managing advisors can see all clients including deleted ones
            if ($authenticatedUserRoleData->hierarchy <= $userRoleHierarchies[UserRole::MANAGING_ADVISOR->value]) {
                return true;
            }
        }
        if ($log === true) {
            $this->logger->notice(
                'User ' . $loggedInUserId . ' tried to read client but isn\'t allowed.'
            );
        }

        return false;
    }

    /**
     * Instead of isGrantedToListClient this function checks
     * with isGrantedToReadClient and removes clients that
     * authenticated user may not see.
     *
     * @param ClientResultData[] $clients
     *
     * @return ClientResultData[]
     */
    public function removeNonAuthorizedClientsFromList(array $clients): array
    {
        $authorizedClients = [];
        foreach ($clients as $client) {
            // Check if authenticated user is allowed to read each client and if yes, add it to the return array
            if ($this->isGrantedToRead($client->userId)) {
                $authorizedClients[] = $client;
            }
        }

        return $authorizedClients;
    }
}
