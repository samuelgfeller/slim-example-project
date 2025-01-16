<?php

namespace App\Module\UserActivity\ReadAuthorization;

use App\Core\Application\Data\UserNetworkSessionData;
use App\Module\Authorization\Repository\AuthorizationUserRoleFinderRepository;
use App\Module\User\Enum\UserRole;
use Psr\Log\LoggerInterface;

/**
 * Check if authenticated user is permitted to do actions
 * Roles: newcomer < advisor < managing_advisor < administrator.
 */
final class UserActivityReadAuthorizationChecker
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
     * Check if the authenticated user is allowed to read user activity.
     *
     * @param int $userIdToRead
     * @param bool $log log if forbidden
     *
     * @return bool
     */
    public function isGrantedToReadUserActivity(
        int $userIdToRead,
        bool $log = true,
    ): bool {
        if ($this->loggedInUserId === null) {
            $this->logger->error(
                'loggedInUserId not set while authorization check isGrantedToReadUserActivity $userIdToRead: '
                . $userIdToRead
            );

            return false;
        }

        $authenticatedUserRoleHierarchy = $this->authorizationUserRoleFinderRepository->getRoleHierarchyByUserId(
            $this->loggedInUserId
        );
        // Returns array with role name as key and hierarchy as value ['role_name' => hierarchy_int]
        // * Lower hierarchy number means higher privileged role
        $userRoleHierarchies = $this->authorizationUserRoleFinderRepository->getUserRolesHierarchies();

        $userToReadRoleHierarchy = $this->authorizationUserRoleFinderRepository->getRoleHierarchyByUserId($userIdToRead);

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
