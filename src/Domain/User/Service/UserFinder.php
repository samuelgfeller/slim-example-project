<?php


namespace App\Domain\User\Service;


use App\Domain\Authorization\Privilege;
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
                $userResultData->availableUserRoles = $this->userAuthorizationGetter->getAuthorizedUserRolesForUser(
                    $userResultData->id,
                    $userResultData->user_role_id
                );
                // If there are more available roles than the attributed one, it means that user has privilege to update roles
                if (count($userResultData->availableUserRoles) > 1) {
                    $userResultData->userRolePrivilege = Privilege::UPDATE;
                } else {
                    $userResultData->userRolePrivilege = Privilege::READ;
                }

                // Check if user is allowed to change status
                if ($this->userAuthorizationChecker->isGrantedToUpdate(['status' => 'value'], $userResultData->id)){
                    $userResultData->statusPrivilege = Privilege::UPDATE;
                }else{

                    $userResultData->statusPrivilege = Privilege::READ;
                }
            }else{
                unset($userResultArray[$key]);
            }
        }

        return $userResultArray;
    }

    /**
     * @param string $id
     * @param bool $withPasswordHash
     * @return UserData
     */
    public function findUserById(string $id, bool $withPasswordHash = false): UserData
    {
        // Find user in database
        $user = $this->userFinderRepository->findUserById($id);

        // If the password hash is not explicitly needed remove it from object for view and other use cases
        if ($withPasswordHash === false) {
            $user->passwordHash = null;
        }
        return $user;
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
