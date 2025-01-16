<?php

namespace App\Module\Note\Update\Service;

use App\Module\Authorization\Repository\AuthorizationUserRoleFinderRepository;
use App\Module\User\Enum\UserRole;
use Odan\Session\SessionInterface;
use Psr\Log\LoggerInterface;

final readonly class NoteUpdateAuthorizationChecker
{
    public function __construct(
        private SessionInterface $session,
        private AuthorizationUserRoleFinderRepository $authorizationUserRoleFinderRepository,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Check if an authenticated user is allowed to update note.
     *
     * @param int $isMain
     * @param int|null $noteOwnerId optional owner id when main note
     * @param int|null $clientOwnerId client owner might become relevant
     * @param bool $log
     *
     * @return bool
     */
    public function isGrantedToUpdate(
        int $isMain,
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
            if (($isMain === 0 && ($loggedInUserId === $noteOwnerId
                        || $authenticatedUserRoleHierarchy <= $userRoleHierarchies[UserRole::MANAGING_ADVISOR->value]))
                // If it's a main note, advisors and higher may edit it and $clientOwnerId could be relevant here
                || ($isMain === 1 // Should be identical to client update basic info authorization
                    && $authenticatedUserRoleHierarchy <= $userRoleHierarchies[UserRole::ADVISOR->value])
            ) {
                return true;
            }
        }

        // User does not have the necessary rights to access area or function
        if ($log === true) {
            $this->logger->notice(
                'User ' . $loggedInUserId . ' tried to update note but isn\'t allowed'
            );
        }

        return false;
    }
}
