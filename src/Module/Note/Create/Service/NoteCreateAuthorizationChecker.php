<?php

namespace App\Module\Note\Create\Service;

use App\Module\Authorization\Repository\AuthorizationUserRoleFinderRepository;
use App\Module\User\Enum\UserRole;
use Odan\Session\SessionInterface;
use Psr\Log\LoggerInterface;

final readonly class NoteCreateAuthorizationChecker
{
    public function __construct(
        private SessionInterface $session,
        private AuthorizationUserRoleFinderRepository $authorizationUserRoleFinderRepository,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Check if an authenticated user is allowed to create note.
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
            $authenticatedUserRoleHierarchy = $this->authorizationUserRoleFinderRepository->getRoleHierarchyByUserId(
                $loggedInUserId
            );
            // Returns array with role name as key and hierarchy as value ['role_name' => hierarchy_int]
            // * Lower hierarchy number means higher privileged role
            $userRoleHierarchies = $this->authorizationUserRoleFinderRepository->getUserRolesHierarchies();
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
}
