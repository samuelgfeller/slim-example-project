<?php

namespace App\Domain\Note\Service\Authorization;

use App\Domain\Authentication\Repository\UserRoleFinderRepository;
use App\Domain\User\Enum\UserRole;
use Odan\Session\SessionInterface;
use Psr\Log\LoggerInterface;

class NotePermissionVerifier
{
    public function __construct(
        private readonly SessionInterface $session,
        private readonly UserRoleFinderRepository $userRoleFinderRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Check if the authenticated user is allowed to read note.
     *
     * @param int $isMain
     * @param int|null $noteOwnerId optional owner id when main note
     * @param int|null $clientOwnerId client owner might become relevant
     * @param int|null $isHidden when note message is hidden
     * @param bool $log
     *
     * @return bool
     */
    public function isGrantedToRead(
        int $isMain = 0,
        ?int $noteOwnerId = null,
        ?int $clientOwnerId = null,
        ?int $isHidden = null,
        bool $log = true
    ): bool {
        if (($loggedInUserId = (int)$this->session->get('user_id')) !== 0) {
            $authenticatedUserRoleHierarchy = $this->userRoleFinderRepository->getRoleHierarchyByUserId(
                $loggedInUserId
            );
            // Returns array with role name as key and hierarchy as value ['role_name' => hierarchy_int]
            // * Lower hierarchy number means higher privileged role
            $userRoleHierarchies = $this->userRoleFinderRepository->getUserRolesHierarchies();
            // newcomers may see all notes and main notes
            if (($authenticatedUserRoleHierarchy <= $userRoleHierarchies[UserRole::NEWCOMER->value])
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

    /**
     * Check if authenticated user is allowed to create note.
     *
     * @param int $isMain
     * @param int|null $clientOwnerId client owner might become relevant
     * @param bool $log
     *
     * @return bool
     */
    public function isGrantedToCreate(int $isMain = 0, ?int $clientOwnerId = null, bool $log = true): bool
    {
        if (($loggedInUserId = (int)$this->session->get('user_id')) !== 0) {
            $authenticatedUserRoleHierarchy = $this->userRoleFinderRepository->getRoleHierarchyByUserId(
                $loggedInUserId
            );
            // Returns array with role name as key and hierarchy as value ['role_name' => hierarchy_int]
            // * Lower hierarchy number means higher privileged role
            $userRoleHierarchies = $this->userRoleFinderRepository->getUserRolesHierarchies();
            if (($isMain === 0 // newcomers may see create notes for any client
                    && $authenticatedUserRoleHierarchy <= $userRoleHierarchies[UserRole::NEWCOMER->value])
                || ($isMain === 1 // only advisors and higher may create main notes
                    && $authenticatedUserRoleHierarchy <= $userRoleHierarchies[UserRole::ADVISOR->value])) {
                return true;
            }
        }

        if ($log === true) {
            $this->logger->notice(
                'User ' . $loggedInUserId . ' tried to create note but isn\'t allowed'
            );
        }

        return false;
    }

    /**
     * Check if authenticated user is allowed to update note.
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
        bool $log = true
    ): bool {
        if (($loggedInUserId = (int)$this->session->get('user_id')) !== 0) {
            $authenticatedUserRoleHierarchy = $this->userRoleFinderRepository->getRoleHierarchyByUserId(
                $loggedInUserId
            );
            // Returns array with role name as key and hierarchy as value ['role_name' => hierarchy_int]
            // * Lower hierarchy number means higher privileged role
            $userRoleHierarchies = $this->userRoleFinderRepository->getUserRolesHierarchies();

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

        // User does not have needed rights to access area or function
        if ($log === true) {
            $this->logger->notice(
                'User ' . $loggedInUserId . ' tried to update note but isn\'t allowed'
            );
        }

        return false;
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
        bool $log = true
    ): bool {
        if (($loggedInUserId = (int)$this->session->get('user_id')) !== 0) {
            $authenticatedUserRoleHierarchy = $this->userRoleFinderRepository->getRoleHierarchyByUserId(
                $loggedInUserId
            );
            // Returns array with role name as key and hierarchy as value ['role_name' => hierarchy_int]
            // * Lower hierarchy number means higher privileged role
            $userRoleHierarchies = $this->userRoleFinderRepository->getUserRolesHierarchies();

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
