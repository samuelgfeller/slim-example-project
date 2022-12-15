<?php

namespace App\Domain\Authorization;

use App\Domain\User\Enum\UserRole;
use App\Infrastructure\Authentication\UserRoleFinderRepository;
use Odan\Session\SessionInterface;

/**
 * Default authorization checker.
 */
class AuthorizationChecker
{
    public function __construct(
        private readonly SessionInterface $session,
        private readonly UserRoleFinderRepository $userRoleFinderRepository,
    ) {
    }

    /**
     * Returns boolean if authenticated user has given role
     * or is higher privileged.
     *
     * @param UserRole $minimalRequiredRole
     *
     * @return bool
     */
    public function isAuthorizedByRole(UserRole $minimalRequiredRole): bool
    {
        $loggedInUserId = $this->session->get('user_id');
        $authenticatedUserRoleData = $this->userRoleFinderRepository->getUserRoleDataFromUser($loggedInUserId);
        /** @var array{role_name: int} $userRoleHierarchies role name as key and hierarchy value
         * lower hierarchy number means higher privilege */
        $userRoleHierarchies = $this->userRoleFinderRepository->getUserRolesHierarchies();

        return $authenticatedUserRoleData->hierarchy <= $userRoleHierarchies[$minimalRequiredRole->value];
    }
}
