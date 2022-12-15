<?php

namespace App\Domain\Note\Authorization;

use App\Domain\Factory\LoggerFactory;
use App\Domain\User\Enum\UserRole;
use App\Infrastructure\Authentication\UserRoleFinderRepository;
use Odan\Session\SessionInterface;
use Psr\Log\LoggerInterface;

class NoteAuthorizationChecker
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly SessionInterface $session,
        private readonly UserRoleFinderRepository $userRoleFinderRepository,
        LoggerFactory $loggerFactory
    ) {
        $this->logger = $loggerFactory->addFileHandler('error.log')->createInstance('note-authorization');
    }

    /**
     * Check if authenticated user is allowed to read note.
     *
     * @param int $isMain
     * @param int|null $noteOwnerId optional owner id when main note
     * @param int|null $clientOwnerId client owner might become relevant
     * @param int|null $hidden when note message is hidden
     * @param bool $log
     *
     * @return bool
     */
    public function isGrantedToRead(
        int $isMain = 0,
        ?int $noteOwnerId = null,
        ?int $clientOwnerId = null,
        ?int $hidden = null,
        bool $log = true
    ): bool {
        if (($loggedInUserId = (int)$this->session->get('user_id')) !== 0) {
            $authenticatedUserRoleData = $this->userRoleFinderRepository->getUserRoleDataFromUser(
                $loggedInUserId
            );
            /** @var array{role_name: int} $userRoleHierarchies lower hierarchy number means higher privilege */
            $userRoleHierarchies = $this->userRoleFinderRepository->getUserRolesHierarchies();
            // newcomers may see all notes and main notes
            if (($authenticatedUserRoleData->hierarchy <= $userRoleHierarchies[UserRole::NEWCOMER->value]) &&
                (
                    in_array($hidden, [null, 0], true) || // When hidden is not null or 0, user has to be advisor to read note
                    $authenticatedUserRoleData->hierarchy <= $userRoleHierarchies[UserRole::ADVISOR->value] ||
                    // If authenticated user is client owner or note owner -> granted to read hidden notes
                    $loggedInUserId === $clientOwnerId || $loggedInUserId === $noteOwnerId
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
            $authenticatedUserRoleData = $this->userRoleFinderRepository->getUserRoleDataFromUser(
                $loggedInUserId
            );
            /** @var array{role_name: int} $userRoleHierarchies lower hierarchy number means higher privilege */
            $userRoleHierarchies = $this->userRoleFinderRepository->getUserRolesHierarchies();
            if (($isMain === 0 && // newcomers may see create notes for any client
                    $authenticatedUserRoleData->hierarchy <= $userRoleHierarchies[UserRole::NEWCOMER->value]) ||
                ($isMain === 1 && // only advisors and higher may create main notes
                    $authenticatedUserRoleData->hierarchy <= $userRoleHierarchies[UserRole::ADVISOR->value])) {
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
            $authenticatedUserRoleData = $this->userRoleFinderRepository->getUserRoleDataFromUser(
                $loggedInUserId
            );
            /** @var array{role_name: int} $userRoleHierarchies lower hierarchy number means higher privilege */
            $userRoleHierarchies = $this->userRoleFinderRepository->getUserRolesHierarchies();

            // If owner or logged-in hierarchy value is smaller or equal managing_advisor -> granted to update
            if (($isMain === 0 && ($loggedInUserId === $noteOwnerId ||
                        $authenticatedUserRoleData->hierarchy <= $userRoleHierarchies[UserRole::MANAGING_ADVISOR->value])) ||
                // If it's a main note, advisors and higher may edit it and $clientOwnerId could be relevant here
                ($isMain === 1 && // Should be identical to client update basic info authorization
                    $authenticatedUserRoleData->hierarchy <= $userRoleHierarchies[UserRole::ADVISOR->value])
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
            $authenticatedUserRoleData = $this->userRoleFinderRepository->getUserRoleDataFromUser(
                $loggedInUserId
            );
            /** @var array{role_name: int} $userRoleHierarchies lower hierarchy number means higher privilege */
            $userRoleHierarchies = $this->userRoleFinderRepository->getUserRolesHierarchies();

            // If owner or logged-in hierarchy value is smaller or equal managing_advisor -> granted to update
            if ($loggedInUserId === $noteOwnerId ||
                $authenticatedUserRoleData->hierarchy <= $userRoleHierarchies[UserRole::MANAGING_ADVISOR->value]) {
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
