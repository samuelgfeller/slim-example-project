<?php

namespace App\Domain\Client\Service;

use App\Domain\Client\Data\ClientDropdownValuesData;
use App\Domain\Client\Repository\ClientStatus\ClientStatusFinderRepository;
use App\Domain\Client\Service\Authorization\ClientPermissionVerifier;
use App\Domain\User\Repository\UserFinderRepository;
use App\Domain\User\Service\UserNameAbbreviator;

readonly class ClientUtilFinder
{
    public function __construct(
        private UserFinderRepository $userFinderRepository,
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
        $allUsers = $this->userFinderRepository->findAllUsers();
        // Filter users that authenticated user is authorized to assign to the client that is being created.
        // Available user roles for dropdown and privilege
        $grantedAssignableUsers = [];
        foreach ($allUsers as $userData) {
            if (// If the user is already assigned to client the value is added so that it's displayed in the dropdown
                // for it to be visible in GUI even if select is greyed out
                ($alreadyAssignedUserId !== null && $userData->id === $alreadyAssignedUserId)
                // Check if authenticated user is allowed to assign the currently iterating user to a client
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
