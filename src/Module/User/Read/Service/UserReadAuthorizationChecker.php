<?php

namespace App\Module\User\Read\Service;

use App\Core\Application\Data\UserNetworkSessionData;
use App\Module\Authorization\Repository\AuthorizationUserRoleFinderRepository;
use App\Module\User\Enum\UserRole;
use Psr\Log\LoggerInterface;

/**
 * Check if authenticated user is permitted to do actions
 * Roles: newcomer < advisor < managing_advisor < administrator.
 */
final class UserReadAuthorizationChecker
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
     * Check if authenticated user is allowed to read user.
     *
     * @param int|null $userIdToRead null when check for all users
     * @param bool $log log if forbidden (expected false when function is called for privilege setting)
     *
     * @return bool
     */
    public function isGrantedToRead(?int $userIdToRead = null, bool $log = true): bool
    {
        if ($this->loggedInUserId === null) {
            $this->logger->error(
                'loggedInUserId not set while authorization check isGrantedToRead $userIdToRead: '
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

        // Only managing advisor and higher privileged are allowed to see other users
        // If the user role hierarchy of the authenticated user is lower or equal
        // than the one from the managing advisor -> authorized
        if ($authenticatedUserRoleHierarchy <= $userRoleHierarchies[UserRole::MANAGING_ADVISOR->value]
            // or user wants to view his own profile in which case also -> authorized
            || $this->loggedInUserId === $userIdToRead) {
            return true;
        }

        if ($log === true) {
            $this->logger->notice('User ' . $this->loggedInUserId . ' tried to read user but isn\'t allowed.');
        }

        return false;
    }
}
