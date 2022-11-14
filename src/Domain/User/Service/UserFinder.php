<?php


namespace App\Domain\User\Service;


use App\Domain\Exceptions\ForbiddenException;
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
                $privilegeAndAuthorizedRoles = $this->userAuthorizationGetter->getPrivilegeAndAuthorizedUserRolesForUser(
                    $userResultData->id,
                    $userResultData->user_role_id
                );
                $userResultData->availableUserRoles = $privilegeAndAuthorizedRoles['userRoles'];
                $userResultData->userRolePrivilege = $privilegeAndAuthorizedRoles['privilege'];

                // Check if user is allowed to change status
                $userResultData->statusPrivilege = $this->userAuthorizationGetter->getUpdatePrivilegeForUserColumn(
                    'status',
                    $userResultData->id
                );
            } else {
                unset($userResultArray[$key]);
            }
        }

        return $userResultArray;
    }

    /**
     * @param string|int $id
     * @return UserData
     */
    public function findUserById(string|int $id): UserData
    {
        // Find user in database and return object
        // $notRestricted true as values are safe as they come from the database. It's not a user input.
        return new UserData($this->userFinderRepository->findUserById((int)$id), true);
    }


    /**
     * Find user with authorization check and privilege attributes
     *
     * @param int $id
     * @return UserResultData
     */
    public function findUserReadResult(int $id): UserResultData
    {
        if ($this->userAuthorizationChecker->isGrantedToRead($id)) {
            $userResultData = new UserResultData($this->userFinderRepository->findUserById($id), true);
            // Status privilege
            $userResultData->statusPrivilege = $this->userAuthorizationGetter->getUpdatePrivilegeForUserColumn(
                'status',
                $id
            );
            // Available user roles for dropdown and privilege
            $privilegeAndAuthorizedRoles = $this->userAuthorizationGetter->getPrivilegeAndAuthorizedUserRolesForUser(
                $userResultData->id,
                $userResultData->user_role_id
            );
            $userResultData->userRolePrivilege = $privilegeAndAuthorizedRoles['privilege'];
            $userResultData->availableUserRoles = $privilegeAndAuthorizedRoles['userRoles'];
            // General data privilege like first name, email and so on
            $userResultData->generalPrivilege = $this->userAuthorizationGetter->getUpdatePrivilegeForUserColumn(
                'general_data',
                $id
            );
            // Password change without verification of old password
            $userResultData->passwordWithoutVerificationPrivilege = $this->userAuthorizationGetter->
            getUpdatePrivilegeForUserColumn('password_without_verification', $id);

            return $userResultData;
        }
        throw new ForbiddenException('Not allowed to read user.');
    }


    /**
     * Find user via email
     *
     * @param string $email
     * @return UserData
     */
    public function findUserByEmail(string $email): UserData
    {
        return $this->userFinderRepository->findUserByEmail($email);
    }
}
