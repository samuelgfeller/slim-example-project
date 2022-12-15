<?php

namespace App\Domain\User\Service;

use App\Domain\User\Authorization\UserAuthorizationGetter;
use App\Domain\User\Enum\UserStatus;

class UserUtilFinder
{
    public function __construct(
        private readonly UserAuthorizationGetter $userAuthorizationGetter,
    ) {
    }

    /**
     * Find all dropdown values for a client.
     *
     * @return array
     */
    public function findUserDropdownValues(): array
    {
        return [
            'userRoles' => $this->userAuthorizationGetter->getAuthorizedUserRoles(),
            'statuses' => UserStatus::toArray(),
        ];
    }
}
