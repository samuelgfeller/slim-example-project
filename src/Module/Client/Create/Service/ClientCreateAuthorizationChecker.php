<?php

namespace App\Module\Client\Create\Service;

use App\Core\Application\Data\UserNetworkSessionData;
use App\Module\Authorization\Repository\AuthorizationUserRoleFinderRepository;
use App\Module\Client\AssignUser\ClientAssignUserAuthorizationChecker;
use App\Module\Client\Data\ClientData;
use App\Module\User\Enum\UserRole;
use Psr\Log\LoggerInterface;

/**
 * Check if the authenticated user is permitted to do actions.
 * Roles: newcomer < advisor < managing_advisor < administrator.
 */
final class ClientCreateAuthorizationChecker
{
    private ?int $loggedInUserId = null;

    public function __construct(
        private readonly AuthorizationUserRoleFinderRepository $authorizationUserRoleFinderRepository,
        private readonly ClientAssignUserAuthorizationChecker $clientAssignUserAuthorizationChecker,
        private readonly UserNetworkSessionData $userNetworkSessionData,
        private readonly LoggerInterface $logger,
    ) {
        $this->loggedInUserId = $this->userNetworkSessionData->userId;
    }

    /**
     * Check if the authenticated user is allowed to create client.
     *
     * @param ClientData|null $client null if check before actual client creation
     *  request otherwise it has to be provided
     *
     * @return bool
     */
    public function isGrantedToCreate(?ClientData $client = null): bool
    {
        if ($this->loggedInUserId === null) {
            $this->logger->error(
                'loggedInUserId not set while isGrantedToCreate authorization check $client: '
                . json_encode($client, JSON_PARTIAL_OUTPUT_ON_ERROR)
            );

            return false;
        }
        $authenticatedUserRoleHierarchy = $this->authorizationUserRoleFinderRepository->getRoleHierarchyByUserId(
            $this->loggedInUserId
        );
        // Returns array with role name as key and hierarchy as value ['role_name' => hierarchy_int]
        // * Lower hierarchy number means higher privileged role
        $userRoleHierarchies = $this->authorizationUserRoleFinderRepository->getUserRolesHierarchies();

        // Newcomer is not allowed to do anything
        // If hierarchy number is greater or equals newcomer it means that user is not allowed
        if ($authenticatedUserRoleHierarchy <= $userRoleHierarchies[UserRole::ADVISOR->value]) {
            // Advisor may create clients but can only assign them to themself or leave it unassigned.
            // If $client is null (not provided), advisor is authorized (being used to check if create btn should
            // be displayed in template)
            if ($client === null ||
                $this->clientAssignUserAuthorizationChecker->isGrantedToAssignUserToClient(
                    $client->userId,
                    $authenticatedUserRoleHierarchy,
                    $userRoleHierarchies
                )) {
                // If authenticated user is at least advisor and client user id is themself, or it's a
                // managing_advisor (logic in isGrantedToAssignUserToClient) -> granted to create
                return true;
            }
        }

        $this->logger->notice('User ' . $this->loggedInUserId . ' tried to create client but isn\'t allowed.');

        return false;
    }
}
