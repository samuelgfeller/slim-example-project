<?php

namespace App\Module\User\Find\Service;

use App\Module\User\Data\UserData;
use App\Module\User\Find\Repository\UserFinderRepository;

// Class cannot be readonly as it's mocked (doubled) in tests
class UserFinder
{
    public function __construct(
        private readonly UserFinderRepository $userFinderRepository,
    ) {
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
}
