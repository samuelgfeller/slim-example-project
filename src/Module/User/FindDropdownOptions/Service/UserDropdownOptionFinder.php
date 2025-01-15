<?php

namespace App\Module\User\FindDropdownOptions\Service;

use App\Module\User\Authorization\Service\AuthorizedUserRoleFilterer;
use App\Module\User\Enum\UserLang;
use App\Module\User\Enum\UserStatus;

final readonly class UserDropdownOptionFinder
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
