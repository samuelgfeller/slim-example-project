<?php

namespace App\Modules\Client\Domain\Service;

use App\Modules\Client\Data\ClientDropdownValuesData;
use App\Modules\Client\Domain\Service\Authorization\ClientPermissionVerifier;
use App\Modules\Client\Repository\ClientStatus\ClientStatusFinderRepository;
use App\Modules\User\Repository\UserFinderRepository;
use App\Modules\User\Service\UserNameAbbreviator;

final readonly class ClientUtilFinder
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
