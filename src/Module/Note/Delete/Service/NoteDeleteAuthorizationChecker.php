<?php

namespace App\Module\Note\Delete\Service;

use App\Module\Authorization\Repository\AuthorizationUserRoleFinderRepository;
use App\Module\User\Enum\UserRole;
use Odan\Session\SessionInterface;
use Psr\Log\LoggerInterface;

final readonly class NoteDeleteAuthorizationChecker
{
    public function __construct(
        private SessionInterface $session,
        private AuthorizationUserRoleFinderRepository $authorizationUserRoleFinderRepository,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Check if authenticated user is allowed to delete note
     * Main note is not deletable so no need for this argument.
     *
     * @param int|null $noteOwnerId
     * @param int|null $clientOwnerId
     * @param bool $log
     *
     * @return bool
     */
    public function isGrantedToDelete(
        ?int $noteOwnerId = null,
        ?int $clientOwnerId = null,
        bool $log = true,
    ): bool {
        if (($loggedInUserId = (int)$this->session->get('user_id')) !== 0) {
            $authenticatedUserRoleHierarchy = $this->authorizationUserRoleFinderRepository->getRoleHierarchyByUserId(
                $loggedInUserId
            );
            // Returns array with role name as key and hierarchy as value ['role_name' => hierarchy_int]
            // * Lower hierarchy number means higher privileged role
            $userRoleHierarchies = $this->authorizationUserRoleFinderRepository->getUserRolesHierarchies();

            // If owner or logged-in hierarchy value is smaller or equal managing_advisor -> granted to update
            if ($loggedInUserId === $noteOwnerId
                || $authenticatedUserRoleHierarchy <= $userRoleHierarchies[UserRole::MANAGING_ADVISOR->value]) {
                return true;
            }
        }

        // User does not have needed rights to access area or function
        if ($log === true) {
            $this->logger->notice(
                'User ' . $loggedInUserId . ' tried to delete note but isn\'t allowed'
            );
        }

        return false;
    }
}
