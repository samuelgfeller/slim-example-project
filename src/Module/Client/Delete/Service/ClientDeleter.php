<?php

namespace App\Module\Client\Delete\Service;

use App\Module\Authorization\Exception\ForbiddenException;
use App\Module\Client\Authorization\Service\ClientPermissionVerifier;
use App\Module\Client\Delete\Repository\ClientDeleterRepository;
use App\Module\Client\FindOwner\ClientOwnerFinderRepository;
use App\Module\Note\Repository\NoteDeleterRepository;
use App\Module\User\Enum\UserActivity;
use App\Module\UserActivity\Service\UserActivityLogger;

final readonly class ClientDeleter
{
    public function __construct(
        private ClientDeleterRepository $clientDeleterRepository,
        private ClientOwnerFinderRepository $clientOwnerFinderRepository,
        private ClientPermissionVerifier $clientPermissionVerifier,
        private UserActivityLogger $userActivityLogger,
        private NoteDeleterRepository $noteDeleterRepository,
    ) {
    }

    /**
     * Delete one client.
     *
     * @param int $clientId
     *
     * @throws ForbiddenException
     *
     * @return bool
     */
    public function deleteClient(int $clientId): bool
    {
        // Find client in db to get its ownership
        $clientOwnerUserId = $this->clientOwnerFinderRepository->findClientOwnerId($clientId);

        if ($this->clientPermissionVerifier->isGrantedToDelete($clientOwnerUserId)) {
            $this->noteDeleterRepository->deleteNotesFromClient($clientId);
            $deleted = $this->clientDeleterRepository->deleteClient($clientId);
            if ($deleted) {
                $this->userActivityLogger->logUserActivity(UserActivity::DELETED, 'client', $clientId);
            }

            return $deleted;
        }

        throw new ForbiddenException('Not allowed to delete client.');
    }
}
