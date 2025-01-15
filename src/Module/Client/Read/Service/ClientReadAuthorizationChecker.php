<?php

namespace App\Module\Client\Read\Service;

use App\Core\Application\Data\UserNetworkSessionData;
use App\Module\Authorization\Repository\AuthorizationUserRoleFinderRepository;
use App\Module\User\Enum\UserRole;
use Psr\Log\LoggerInterface;

/**
 * Check if the authenticated user is permitted to do actions.
 * Roles: newcomer < advisor < managing_advisor < administrator.
 */
final class ClientReadAuthorizationChecker
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
        bool $log = true,
    ): bool {
        if ($this->loggedInUserId !== null) {
            $authenticatedUserRoleHierarchy = $this->authorizationUserRoleFinderRepository->getRoleHierarchyByUserId(
                $this->loggedInUserId
            );
            // Returns array with role name as key and hierarchy as value ['role_name' => hierarchy_int]
            // * Lower hierarchy number means higher privileged role
            $userRoleHierarchies = $this->authorizationUserRoleFinderRepository->getUserRolesHierarchies();

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
