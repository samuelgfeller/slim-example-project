<?php

namespace App\Domain\User\Service;

use App\Domain\User\Enum\UserStatus;
use App\Infrastructure\Authentication\UserRoleFinderRepository;

class UserUtilFinder
{
    public function __construct(
        private readonly UserRoleFinderRepository $userRoleFinderRepository,
    )
    {
    }

    /**
     * Find all dropdown values for a client
     *
     * @return array
     */
    public function findUserDropdownValues(): array
    {
        return [
            'userRoles' => $this->userRoleFinderRepository->findAllUserRolesForDropdown(),
            'statuses' => UserStatus::array()
        ];
    }

}