<?php

namespace App\Module\User\ListPage\Service;

use App\Core\Infrastructure\Database\Hydrator;
use App\Module\User\Authorization\Service\UserPrivilegeDeterminer;
use App\Module\User\Data\UserResultData;
use App\Module\User\FindDropdownOptions\Service\AuthorizedUserRoleFilterer;
use App\Module\User\FindList\UserListFinderRepository;
use App\Module\User\Read\Service\UserReadAuthorizationChecker;

// Class cannot be readonly as it's mocked (doubled) in tests
class UserListPageFinder
{
    public function __construct(
        private readonly UserListFinderRepository $userListFinderRepository,
        private readonly UserPrivilegeDeterminer $userPrivilegeDeterminer,
        private readonly AuthorizedUserRoleFilterer $authorizedUserRoleFilterer,
        private readonly UserReadAuthorizationChecker $userReadAuthorizationChecker,
        private readonly Hydrator $hydrator,
    ) {
    }

    /**
     * @return UserResultData[]
     */
    public function findAllUsersResultDataForList(): array
    {
        $userResultArray = $this->hydrator->hydrate(
            $this->userListFinderRepository->findAllUserRows(),
            UserResultData::class
        );

        foreach ($userResultArray as $key => $userResultData) {
            // Check if authenticated user is allowed to read user
            if ($this->userReadAuthorizationChecker->isGrantedToRead($userResultData->id)) {
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
}
