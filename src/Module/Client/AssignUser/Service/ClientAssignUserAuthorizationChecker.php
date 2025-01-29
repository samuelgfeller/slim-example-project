<?php

namespace App\Module\Client\AssignUser\Service;

use App\Application\Data\UserNetworkSessionData;
use App\Module\Authorization\Repository\AuthorizationUserRoleFinderRepository;
use App\Module\User\Enum\UserRole;
use Psr\Log\LoggerInterface;

/**
 * Check if the authenticated user is permitted to do actions.
 * Roles: newcomer < advisor < managing_advisor < administrator.
 */
final class ClientAssignUserAuthorizationChecker
{
    private ?int $loggedInUserId = null;

    public function __construct(
        private readonly AuthorizationUserRoleFinderRepository $authorizationUserRoleFinderRepository,
        private readonly UserNetworkSessionData $userNetworkSessionData,
        private readonly LoggerInterface $logger,
    ) {
        $this->loggedInUserId = $this->userNetworkSessionData->userId;
    }

    /**
     * Check if the authenticated user is allowed to assign user to client.
     * (Client id not needed as the same rules applies for new clients and all existing clients)
     * In own function to be used to filter dropdown options for frontend.
     *
     * @param int|string|null $assignedUserId
     * @param int|null $authenticatedUserRoleHierarchy optional so that it can be called outside this class
     * @param array|null $userRoleHierarchies optional so that it can be called outside this class
     *
     * @return bool|void
     */
    public function isGrantedToAssignUserToClient(
        int|string|null $assignedUserId,
        ?int $authenticatedUserRoleHierarchy = null,
        ?array $userRoleHierarchies = null,
    ) {
        if ($this->loggedInUserId === null) {
            $this->logger->error(
                'loggedInUserId not set while isGrantedToAssignUserToClient authorization check $assignedUserId: '
                . $assignedUserId
            );

            return false;
        }

        // $authenticatedUserRoleData and $userRoleHierarchies passed as arguments if called inside this class
        if ($authenticatedUserRoleHierarchy === null) {
            $authenticatedUserRoleHierarchy = $this->authorizationUserRoleFinderRepository->getRoleHierarchyByUserId(
                $this->loggedInUserId
            );
        }
        if ($userRoleHierarchies === null) {
            // Returns array with role name as key and hierarchy as value ['role_name' => hierarchy_int]
            // * Lower hierarchy number means higher privileged role
            $userRoleHierarchies = $this->authorizationUserRoleFinderRepository->getUserRolesHierarchies();
        }



        // If hierarchy privilege is greater or equals advisor, it means that user may assign the user to themself
        if ($authenticatedUserRoleHierarchy <= $userRoleHierarchies[UserRole::ADVISOR->value]) {
            // Advisor may create clients but can only assign them to themselves or leave it unassigned
            if ($assignedUserId === $this->loggedInUserId || $assignedUserId === null
                // managing advisor can link user to someone else
                || $authenticatedUserRoleHierarchy <= $userRoleHierarchies[UserRole::MANAGING_ADVISOR->value]) {
                // If authenticated user is at least advisor and client user id is authenticated user himself,
                // null (unassigned) or authenticated user is managing_advisor -> granted to assign
                return true;
            }
        }
    }
}
