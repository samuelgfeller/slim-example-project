<?php

namespace App\Domain\User\Service;

use App\Domain\User\Enum\UserLang;
use App\Domain\User\Enum\UserStatus;
use App\Domain\User\Service\Authorization\AuthorizedUserRoleFilterer;

final readonly class UserUtilFinder
{
    public function __construct(
        private AuthorizedUserRoleFilterer $authorizedUserRoleFilterer,
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
            'userRoles' => $this->authorizedUserRoleFilterer->filterAuthorizedUserRoles(),
            'statuses' => UserStatus::getAllDisplayNames(),
            'languages' => UserLang::toArray(),
        ];
    }
}
