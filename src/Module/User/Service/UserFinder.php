<?php

namespace App\Module\User\Service;

use App\Core\Domain\Exception\DomainRecordNotFoundException;
use App\Module\Authorization\Exception\ForbiddenException;
use App\Module\User\Authorization\AuthorizedUserRoleFilterer;
use App\Module\User\Authorization\UserPermissionVerifier;
use App\Module\User\Authorization\UserPrivilegeDeterminer;
use App\Module\User\Data\UserData;
use App\Module\User\Data\UserResultData;
use App\Module\User\Repository\UserFinderRepository;

// Class cannot be readonly as it's mocked (doubled) in tests
class UserFinder
{
    public function __construct(
        private readonly UserFinderRepository $userFinderRepository,
        private readonly UserPrivilegeDeterminer $userPrivilegeDeterminer,
        private readonly AuthorizedUserRoleFilterer $authorizedUserRoleFilterer,
        private readonly UserPermissionVerifier $userPermissionVerifier,
    ) {
    }

    /**
     * @return UserResultData[]
     */
    public function findAllUsersResultDataForList(): array
    {
        $userResultArray = $this->userFinderRepository->findAllUsersForList();

        foreach ($userResultArray as $key => $userResultData) {
            // Check if authenticated user is allowed to read user
            if ($this->userPermissionVerifier->isGrantedToRead($userResultData->id)) {
                // Authorization limits which entries are in the user role dropdown
                $userResultData->availableUserRoles = $this->authorizedUserRoleFilterer->filterAuthorizedUserRoles(
                    $userResultData->userRoleId
                );
                $userResultData->userRolePrivilege = $this->userPrivilegeDeterminer->getUserRoleAssignmentPrivilege(
                    $userResultData->availableUserRoles
                );

                // Check if user is allowed to change status
                $userResultData->statusPrivilege = $this->userPrivilegeDeterminer->getMutationPrivilege(
                    (int)$userResultData->id,
                    'status',
                );
                // Personal info privilege like first name, email and so on no needed for list
                // $userResultData->generalPrivilege = $this->userPermissionVerifier->getUpdatePrivilegeForUserColumn(
                // 'personal_info', $userResultData->id );
            } else {
                unset($userResultArray[$key]);
            }
        }

        return $userResultArray;
    }

    /**
     * @param string|int|null $id
     *
     * @return UserData
     */
    public function findUserById(string|int|null $id): UserData
    {
        // Find user in database and return object
        return $id ? new UserData($this->userFinderRepository->findUserById((int)$id)) : new UserData();
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
