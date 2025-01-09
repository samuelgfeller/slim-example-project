<?php

namespace App\Module\Client\DropdownFinder\Service;

use App\Module\Client\Authorization\Service\ClientPermissionVerifier;
use App\Module\Client\ClientStatus\Repository\ClientStatusFinderRepository;
use App\Module\Client\DropdownFinder\Data\ClientDropdownValuesData;
use App\Module\Client\DropdownFinder\Repository\ClientUserDropdownFinderRepository;
use App\Module\User\Service\UserNameAbbreviator;

final readonly class ClientDropdownFinder
{
    public function __construct(
        private ClientUserDropdownFinderRepository $clientUserDropdownFinderRepository,
        private UserNameAbbreviator $userNameAbbreviator,
        private ClientStatusFinderRepository $clientStatusFinderRepository,
        private ClientPermissionVerifier $clientPermissionVerifier,
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
        $allUsers = $this->clientUserDropdownFinderRepository->findAllUsers();
        // Filter users, which the authenticated user is authorized to assign to a client.
        // Available user roles for dropdown and privilege.
        $grantedAssignableUsers = [];
        foreach ($allUsers as $userData) {
            if (// If the user is already assigned to the client, the value is added so that it's displayed in the
                // dropdown for it to be visible in the user interface, even if the <select> is greyed out
                ($alreadyAssignedUserId !== null && $userData->id === $alreadyAssignedUserId)
                // Check if the authenticated user is allowed to assign the currently iterating user to a client
                || $this->clientPermissionVerifier->isGrantedToAssignUserToClient($userData->id)
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
