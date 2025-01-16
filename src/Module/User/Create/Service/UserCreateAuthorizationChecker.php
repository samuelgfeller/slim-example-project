<?php

namespace App\Module\User\Create\Service;

use App\Core\Application\Data\UserNetworkSessionData;
use App\Module\Authorization\Repository\AuthorizationUserRoleFinderRepository;
use App\Module\User\AssignRole\UserAssignRoleAuthorizationChecker;
use App\Module\User\Enum\UserRole;
use Psr\Log\LoggerInterface;

/**
 * Check if authenticated user is permitted to do actions
 * Roles: newcomer < advisor < managing_advisor < administrator.
 */
final class UserCreateAuthorizationChecker
{
    private ?int $loggedInUserId = null;

    public function __construct(
        private readonly UserNetworkSessionData $userNetworkSessionData,
        private readonly AuthorizationUserRoleFinderRepository $authorizationUserRoleFinderRepository,
        private readonly UserAssignRoleAuthorizationChecker $userAssignRoleAuthorizationChecker,
        private readonly LoggerInterface $logger,
    ) {
        // Fix error $userId must not be accessed before initialization
        $this->loggedInUserId = $this->userNetworkSessionData->userId ?? null;
    }

    /**
     * Check if the authenticated user is allowed to create
     * Important to have user role in the object.
     *
     * @param array $userValues
     *
     * @return bool
     */
    public function isGrantedToCreate(array $userValues): bool
    {
        if ($this->loggedInUserId === null) {
            $this->logger->error(
                'loggedInUserId not set while authorization check isGrantedToCreate: '
                . json_encode($userValues, JSON_PARTIAL_OUTPUT_ON_ERROR)
            );

            return false;
        }
        $authenticatedUserRoleHierarchy = $this->authorizationUserRoleFinderRepository->getRoleHierarchyByUserId(
            $this->loggedInUserId
        );
        // Returns array with role name as key and hierarchy as value ['role_name' => hierarchy_int]
        // * Lower hierarchy number means higher privileged role
        $userRoleHierarchies = $this->authorizationUserRoleFinderRepository->getUserRolesHierarchies();

        // Newcomer and advisor are not allowed to do anything from other users - only user edit his own profile
        // Managing advisor may change users
        if ($authenticatedUserRoleHierarchy <= $userRoleHierarchies[UserRole::MANAGING_ADVISOR->value]) {
            // Managing advisors can do everything with users except setting a role higher than advisor
            if ($this->userAssignRoleAuthorizationChecker->userRoleIsGranted(
                $userValues['user_role_id'] ?? null,
                null,
                $authenticatedUserRoleHierarchy,
                $userRoleHierarchies
            ) === true
            ) {
                return true;
            }

            // If the user role of the user managing advisors or higher wants to change is empty, allowed
            // It's the validation's job to check if the value is valid
            if ($userValues['user_role_id'] === null) {
                return true;
            }
        }
        // There is no need to check if user wants to create his own user as he can't be logged in if the user doesn't exist

        $this->logger->notice(
            'User ' . $this->loggedInUserId . ' tried to create user but isn\'t allowed.'
        );

        return false;
    }
}
