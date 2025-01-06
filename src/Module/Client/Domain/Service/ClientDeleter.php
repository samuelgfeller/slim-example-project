<?php

namespace App\Module\Client\Domain\Service;

use App\Module\Authorization\Exception\ForbiddenException;
use App\Module\Client\Domain\Service\Authorization\ClientPermissionVerifier;
use App\Module\Client\Repository\ClientDeleterRepository;
use App\Module\Note\Repository\NoteDeleterRepository;
use App\Module\User\Enum\UserActivity;
use App\Module\UserActivity\Service\UserActivityLogger;

final readonly class ClientDeleter
{
    public function __construct(
        private ClientDeleterRepository $clientDeleterRepository,
        private ClientFinder $clientFinder,
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
        $clientFromDb = $this->clientFinder->findClient($clientId);

        if ($this->clientPermissionVerifier->isGrantedToDelete($clientFromDb->userId)) {
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
