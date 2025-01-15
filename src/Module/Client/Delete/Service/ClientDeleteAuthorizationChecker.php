<?php

namespace App\Module\Client\Delete\Service;

use App\Core\Application\Data\UserNetworkSessionData;
use App\Module\Authorization\Repository\AuthorizationUserRoleFinderRepository;
use Psr\Log\LoggerInterface;

/**
 * Check if the authenticated user is permitted to do actions.
 * Roles: newcomer < advisor < managing_advisor < administrator.
 */
final class ClientDeleteAuthorizationChecker
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
     * Check if the authenticated user is allowed to delete client.
     *
     * @param int|null $ownerId
     * @param bool $log log if forbidden (expected false when function is called for privilege setting)
     *
     * @return bool
     */
    public function isGrantedToDelete(?int $ownerId, bool $log = true): bool
    {
        if ($this->loggedInUserId === null) {
            $this->logger->error('loggedInUserId not set while isGrantedToDelete authorization check');

            return false;
        }
        $authenticatedUserRoleHierarchy = $this->authorizationUserRoleFinderRepository->getRoleHierarchyByUserId(
            $this->loggedInUserId
        );
        // Returns array with role name as key and hierarchy as value ['role_name' => hierarchy_int]
        // * Lower hierarchy number means higher privileged role
        $userRoleHierarchies = $this->authorizationUserRoleFinderRepository->getUserRolesHierarchies();

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
}
