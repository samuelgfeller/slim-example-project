<?php

namespace App\Module\User\Read\Service;

use App\Core\Domain\Exception\DomainRecordNotFoundException;
use App\Module\Authorization\Exception\ForbiddenException;
use App\Module\User\Authorization\AuthorizedUserRoleFilterer;
use App\Module\User\Authorization\UserPermissionVerifier;
use App\Module\User\Authorization\UserPrivilegeDeterminer;
use App\Module\User\Data\UserResultData;
use App\Module\User\Find\Repository\UserFinderRepository;

// Class cannot be readonly as it's mocked (doubled) in tests
readonly class UserReadFinder
{
    public function __construct(
        private UserFinderRepository $userFinderRepository,
        private UserPrivilegeDeterminer $userPrivilegeDeterminer,
        private AuthorizedUserRoleFilterer $authorizedUserRoleFilterer,
        private UserPermissionVerifier $userPermissionVerifier,
    ) {
    }

    /**
     * Find user with authorization check and privilege attributes.
     *
     * @param int $id
     *
     * @throws \Exception
     *
     * @return UserResultData
     */
    public function findUserReadResult(int $id): UserResultData
    {
        if ($this->userPermissionVerifier->isGrantedToRead($id)) {
            $userRow = $this->userFinderRepository->findUserById($id);
            if (!empty($userRow)) {
                $userResultData = new UserResultData($userRow);
                // Status privilege
                $userResultData->statusPrivilege = $this->userPrivilegeDeterminer->getMutationPrivilege(
                    $id,
                    'status',
                );
                // Available user roles for dropdown and privilege
                $userResultData->availableUserRoles = $this->authorizedUserRoleFilterer->filterAuthorizedUserRoles(
                    $userResultData->userRoleId
                );
                $userResultData->userRolePrivilege = $this->userPrivilegeDeterminer->getUserRoleAssignmentPrivilege(
                    $userResultData->availableUserRoles
                );

                // Personal info privilege like first name, email and so on
                $userResultData->generalPrivilege = $this->userPrivilegeDeterminer->getMutationPrivilege(
                    $id,
                    'personal_info',
                );
                // Password change without verification of old password
                $userResultData->passwordWithoutVerificationPrivilege = $this->userPrivilegeDeterminer->
                getMutationPrivilege($id, 'password_without_verification');

                return $userResultData;
            }
            // When user allowed to read, and it doesn't exist indicate that the resource was not found
            throw new DomainRecordNotFoundException('User not found.');
        }
        // Forbidden when not found and user is not allowed to read
        throw new ForbiddenException('Not allowed to read user.');
    }
}
