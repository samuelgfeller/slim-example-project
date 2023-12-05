<?php

namespace App\Domain\Client\Service;

use App\Domain\Authentication\Exception\ForbiddenException;
use App\Domain\Client\Repository\ClientDeleterRepository;
use App\Domain\Client\Service\Authorization\ClientPermissionVerifier;
use App\Domain\Note\Repository\NoteDeleterRepository;
use App\Domain\User\Enum\UserActivity;
use App\Domain\UserActivity\Service\UserActivityLogger;

readonly class ClientDeleter
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
     * Delete one post logic.
     *
     * @param int $clientId
     *
     * @throws ForbiddenException
     *
     * @return bool
     */
    public function deleteClient(int $clientId): bool
    {
        // Find post in db to get its ownership
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
