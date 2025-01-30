<?php

namespace App\Module\Note\Read\Service;

use App\Module\Authorization\Repository\AuthorizationUserRoleFinderRepository;
use App\Module\User\Enum\UserRole;
use Odan\Session\SessionInterface;
use Psr\Log\LoggerInterface;

final readonly class NoteReadAuthorizationChecker
{
    public function __construct(
        private SessionInterface $session,
        private AuthorizationUserRoleFinderRepository $authorizationUserRoleFinderRepository,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Check if the authenticated user is allowed to read note.
     *
     * @param int $isMain
     * @param int|null $noteOwnerId optional owner id when main note
     * @param int|null $clientOwnerId client owner user id might become relevant
     * @param int|null $isHidden when note message is hidden
     * @param bool $isDeleted note is deleted
     * @param bool $log
     *
     * @return bool
     */
    public function isGrantedToRead(
        int $isMain = 0,
        ?int $noteOwnerId = null,
        ?int $clientOwnerId = null,
        ?int $isHidden = null,
        bool $isDeleted = false,
        bool $log = true,
    ): bool {
        if (($loggedInUserId = (int)$this->session->get('user_id')) !== 0) {
            $authenticatedUserRoleHierarchy = $this->authorizationUserRoleFinderRepository->getRoleHierarchyByUserId(
                $loggedInUserId
            );
            // Returns array with role name as key and hierarchy as value ['role_name' => hierarchy_int]
            // * Lower hierarchy number means higher privileged role
            $userRoleHierarchies = $this->authorizationUserRoleFinderRepository->getUserRolesHierarchies();
            // newcomers may see all notes and main notes except deleted ones that only managing advisors can see
            if (($authenticatedUserRoleHierarchy <= $userRoleHierarchies[UserRole::NEWCOMER->value])
                && ( // If the note is deleted, authenticated user must be managing advisors or higher
                    $isDeleted === false
                    || $authenticatedUserRoleHierarchy <= $userRoleHierarchies[UserRole::MANAGING_ADVISOR->value]
                )
                && ( // When hidden is not null or 0, user has to be advisor to read note
                    in_array($isHidden, [null, 0, false], true)
                    || $authenticatedUserRoleHierarchy <= $userRoleHierarchies[UserRole::ADVISOR->value]
                    // If authenticated user is client owner or note owner -> granted to read hidden notes
                    || $loggedInUserId === $clientOwnerId || $loggedInUserId === $noteOwnerId
                )
            ) {
                return true;
            }
        }
        if ($log === true) {
            $this->logger->notice(
                'User ' . $loggedInUserId . ' tried to read note but isn\'t allowed'
            );
        }

        return false;
    }
}
