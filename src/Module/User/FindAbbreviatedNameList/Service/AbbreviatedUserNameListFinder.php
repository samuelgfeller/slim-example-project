<?php

namespace App\Module\User\FindAbbreviatedNameList\Service;

use App\Module\User\FindList\Repository\UserListFinderRepository;

readonly class AbbreviatedUserNameListFinder
{
    public function __construct(
        private UserNameAbbreviator $userNameAbbreviator,
        private UserListFinderRepository $userListFinderRepository,
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function findAbbreviatedUserNamesList(): array
    {
        return $this->userNameAbbreviator->abbreviateUserNames(
            $this->userListFinderRepository->findAllUsers()
        );
    }
}
