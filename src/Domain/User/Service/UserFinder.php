<?php

namespace App\Domain\User\Service;

use App\Domain\Authentication\Exception\ForbiddenException;
use App\Domain\Exception\DomainRecordNotFoundException;
use App\Domain\User\Authorization\UserAuthorizationChecker;
use App\Domain\User\Authorization\UserAuthorizationGetter;
use App\Domain\User\Data\UserData;
use App\Domain\User\Data\UserResultData;
use App\Infrastructure\User\UserFinderRepository;

class UserFinder
{
    public function __construct(
        private readonly UserFinderRepository $userFinderRepository,
        private readonly UserAuthorizationGetter $userAuthorizationGetter,
        private readonly UserAuthorizationChecker $userAuthorizationChecker,
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
            if ($this->userAuthorizationChecker->isGrantedToRead($userResultData->id)) {
                // Authorization limits which entries are in the user role dropdown
                $userResultData->availableUserRoles = $this->userAuthorizationGetter->getAuthorizedUserRoles(
                    $userResultData->userRoleId
                );
                $userResultData->userRolePrivilege = $this->userAuthorizationGetter->getUserRoleAttributionPrivilege(
                    $userResultData->availableUserRoles
                );

                // Check if user is allowed to change status
                $userResultData->statusPrivilege = $this->userAuthorizationGetter->getMutationPrivilegeForUserColumn(
                    $userResultData->id,
                    'status',
                );
            // General data privilege like first name, email and so on no needed for list
            // $userResultData->generalPrivilege = $this->userAuthorizationGetter->getUpdatePrivilegeForUserColumn(
                //     'general_data', $userResultData->id );
            } else {
                unset($userResultArray[$key]);
            }
        }

        return $userResultArray;
    }

    /**
     * @param string|int $id
     *
     * @throws \Exception
     *
     * @return UserData
     */
    public function findUserById(string|int $id): UserData
    {
        // Find user in database and return object
        // $notRestricted true as values are safe as they come from the database. It's not a user input.
        return new UserData($this->userFinderRepository->findUserById((int)$id));
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
        if ($this->userAuthorizationChecker->isGrantedToRead($id)) {
            $userRow = $this->userFinderRepository->findUserById($id);
            if (!empty($userRow)) {
                $userResultData = new UserResultData($userRow);
                // Status privilege
                $userResultData->statusPrivilege = $this->userAuthorizationGetter->getMutationPrivilegeForUserColumn(
                    $id,
                    'status',
                );
                // Available user roles for dropdown and privilege
                $userResultData->availableUserRoles = $this->userAuthorizationGetter->getAuthorizedUserRoles(
                    $userResultData->userRoleId
                );
                $userResultData->userRolePrivilege = $this->userAuthorizationGetter->getUserRoleAttributionPrivilege(
                    $userResultData->availableUserRoles
                );

                // General data privilege like first name, email and so on
                $userResultData->generalPrivilege = $this->userAuthorizationGetter->getMutationPrivilegeForUserColumn(
                    $id,
                    'general_data',
                );
                // Password change without verification of old password
                $userResultData->passwordWithoutVerificationPrivilege = $this->userAuthorizationGetter->
                getMutationPrivilegeForUserColumn($id, 'password_without_verification');

                return $userResultData;
            }
            // When user allowed to read, and it doesn't exist indicate that the resource was not found
            throw new DomainRecordNotFoundException('User not found.');
        }
        // Forbidden when not found and user is not allowed to read
        throw new ForbiddenException('Not allowed to read user.');
    }

    /**
     * Find user via email.
     *
     * @param string $email
     *
     * @return UserData
     */
    public function findUserByEmail(string $email): UserData
    {
        return $this->userFinderRepository->findUserByEmail($email);
    }
}
