<?php

namespace App\Domain\User\Service;

use App\Domain\User\Enum\UserLang;
use App\Domain\User\Enum\UserStatus;
use App\Domain\User\Service\Authorization\UserAuthorizationGetter;

class UserUtilFinder
{
    public function __construct(
        private readonly UserAuthorizationGetter $userAuthorizationGetter,
    ) {
    }

    /**
     * Find all dropdown values for user creation form.
     *
     * @return array
     */
    public function findUserDropdownValues(): array
    {
        return [
            'userRoles' => $this->userAuthorizationGetter->getAuthorizedUserRoles(),
            'statuses' => UserStatus::toTranslatedNamesArray(),
            'languages' => UserLang::toArray(),
        ];
    }
}
