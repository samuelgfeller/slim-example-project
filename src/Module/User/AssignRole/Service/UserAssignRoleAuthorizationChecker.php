<?php

namespace App\Module\User\AssignRole\Service;

use App\Application\Data\UserNetworkSessionData;
use App\Module\Authorization\Repository\AuthorizationUserRoleFinderRepository;
use App\Module\User\Enum\UserRole;
use Psr\Log\LoggerInterface;

/**
 * Check if authenticated user is permitted to do actions
 * Roles: newcomer < advisor < managing_advisor < administrator.
 */
final class UserAssignRoleAuthorizationChecker
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
     * Check if the authenticated user is allowed to assign a user role.
     *
     * @param string|int|null $newUserRoleId (New) user role id to be assigned. Nullable as admins are authorized to
     * set any role, validation should check if the value is valid.
     * @param string|int|null $userRoleIdOfUserToMutate (Existing) user role of user to be changed
     * @param int|null $authenticatedUserRoleHierarchy optional so that it can be called outside this class
     * @param array|null $userRoleHierarchies optional so that it can be called outside this class
     *
     * @return bool
     */
    public function userRoleIsGranted(
        string|int|null $newUserRoleId,
        string|int|null $userRoleIdOfUserToMutate,
        ?int $authenticatedUserRoleHierarchy = null,
        ?array $userRoleHierarchies = null,
    ): bool {
        if ($this->loggedInUserId === null) {
            $this->logger->error(
                'loggedInUserId not set while authorization check that user role is granted $userRoleIdOfUserToMutate: '
                . $userRoleIdOfUserToMutate
            );

            return false;
        }
        // $authenticatedUserRoleData and $userRoleHierarchies passed as arguments if called inside this class
        if ($authenticatedUserRoleHierarchy === null) {
            $authenticatedUserRoleHierarchy = $this->authorizationUserRoleFinderRepository->getRoleHierarchyByUserId(
                $this->loggedInUserId
            );
        }
        if ($userRoleHierarchies === null) {
            // Returns array with role name as key and hierarchy as value ['role_name' => hierarchy_int]
            // * Lower hierarchy number means higher privileged role
            $userRoleHierarchies = $this->authorizationUserRoleFinderRepository->getUserRolesHierarchies();
        }

        $userRoleHierarchiesById = $this->authorizationUserRoleFinderRepository->getUserRolesHierarchies(true);

        // Role higher (lower hierarchy number) than managing advisor may assign any role (admin)
        if ($authenticatedUserRoleHierarchy < $userRoleHierarchies[UserRole::MANAGING_ADVISOR->value]) {
            return true;
        }

        if (// Managing advisor can only attribute roles with lower or equal privilege than advisor
            !empty($newUserRoleId)
            && $authenticatedUserRoleHierarchy <= $userRoleHierarchies[UserRole::MANAGING_ADVISOR->value]
            && $userRoleHierarchiesById[$newUserRoleId] >= $userRoleHierarchies[UserRole::ADVISOR->value]
            // And managing advisor may only change advisors or newcomers
            && ($userRoleIdOfUserToMutate === null
                || $userRoleHierarchiesById[$userRoleIdOfUserToMutate] >=
                $userRoleHierarchies[UserRole::ADVISOR->value])
        ) {
            return true;
        }

        return false;
    }
}
