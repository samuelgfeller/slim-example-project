<?php

namespace App\Module\User\Delete\Service;

use App\Application\Data\UserNetworkSessionData;
use App\Module\Authorization\Repository\AuthorizationUserRoleFinderRepository;
use App\Module\User\Enum\UserRole;
use Psr\Log\LoggerInterface;

/**
 * Check if authenticated user is permitted to do actions
 * Roles: newcomer < advisor < managing_advisor < administrator.
 */
final class UserDeleteAuthorizationChecker
{
    private ?int $loggedInUserId = null;

    public function __construct(
        private readonly UserNetworkSessionData $userNetworkSessionData,
        private readonly AuthorizationUserRoleFinderRepository $authorizationUserRoleFinderRepository,
        private readonly LoggerInterface $logger,
    ) {
        // Fix error $userId must not be accessed before initialization
        $this->loggedInUserId = $this->userNetworkSessionData->userId ?? null;
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
        bool $log = true,
    ): bool {
        if ($this->loggedInUserId === null) {
            $this->logger->error(
                'loggedInUserId not set while authorization check isGrantedToDelete $userIdToDelete: '
                . $userIdToDelete
            );

            return false;
        }
        $authenticatedUserRoleHierarchy = $this->authorizationUserRoleFinderRepository->getRoleHierarchyByUserId(
            $this->loggedInUserId
        );
        $userToDeleteRoleHierarchy = $this->authorizationUserRoleFinderRepository->getRoleHierarchyByUserId($userIdToDelete);

        // Returns array with role name as key and hierarchy as value ['role_name' => hierarchy_int]
        // * Lower hierarchy number means higher privileged role
        $userRoleHierarchies = $this->authorizationUserRoleFinderRepository->getUserRolesHierarchies();

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
}
