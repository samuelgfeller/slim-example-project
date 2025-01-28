<?php

namespace App\Module\Client\DropdownFinder\Service;

use App\Module\Client\AssignUser\Service\ClientAssignUserAuthorizationChecker;
use App\Module\Client\ClientStatus\Repository\ClientStatusFinderRepository;
use App\Module\Client\DropdownFinder\Data\ClientDropdownValuesData;
use App\Module\User\FindAbbreviatedNameList\Service\UserNameAbbreviator;
use App\Module\User\FindList\Repository\UserListFinderRepository;

final readonly class ClientDropdownFinder
{
    public function __construct(
        private UserListFinderRepository $userListFinderRepository,
        private UserNameAbbreviator $userNameAbbreviator,
        private ClientStatusFinderRepository $clientStatusFinderRepository,
        private ClientAssignUserAuthorizationChecker $clientAssignUserAuthorizationChecker,
    ) {
    }

    /**
     * Find all dropdown values for a client.
     *
     * @param int|null $alreadyAssignedUserId in case there is already a user assigned to client
     *
     * @return ClientDropdownValuesData
     */
    public function findClientDropdownValues(?int $alreadyAssignedUserId = null): ClientDropdownValuesData
    {
        $allUsers = $this->userListFinderRepository->findAllUsers();
        // Filter users, which the authenticated user is authorized to assign to a client.
        // Available user roles for dropdown and privilege.
        $grantedAssignableUsers = [];
        foreach ($allUsers as $userData) {
            if (// If the user is already assigned to the client, the value is added so that it's displayed in the
                // dropdown for it to be visible in the user interface, even if the <select> is greyed out
                ($alreadyAssignedUserId !== null && $userData->id === $alreadyAssignedUserId)
                // Check if the authenticated user is allowed to assign the currently iterating user to a client
                || $this->clientAssignUserAuthorizationChecker->isGrantedToAssignUserToClient($userData->id)
            ) {
                $grantedAssignableUsers[] = $userData;
            }
        }

        return new ClientDropdownValuesData(
            $this->clientStatusFinderRepository->findAllClientStatusesMappedByIdName(),
            $this->userNameAbbreviator->abbreviateUserNames($grantedAssignableUsers),
        );
    }
}
