<?php

namespace App\Domain\Client\Service;

use App\Domain\Authentication\Exception\ForbiddenException;
use App\Domain\Client\Authorization\ClientAuthorizationChecker;
use App\Domain\User\Enum\UserActivity;
use App\Domain\User\Service\UserActivityManager;
use App\Infrastructure\Client\ClientDeleterRepository;
use App\Infrastructure\Note\NoteDeleterRepository;

class ClientDeleter
{
    public function __construct(
        private readonly ClientDeleterRepository $clientDeleterRepository,
        private readonly ClientFinder $clientFinder,
        private readonly ClientAuthorizationChecker $clientAuthorizationChecker,
        private readonly UserActivityManager $userActivityManager,
        private readonly NoteDeleterRepository $noteDeleterRepository,
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

        if ($this->clientAuthorizationChecker->isGrantedToDelete($clientFromDb->userId)) {
            $this->noteDeleterRepository->deleteNotesFromClient($clientId);
            $deleted = $this->clientDeleterRepository->deleteClient($clientId);
            if ($deleted) {
                $this->userActivityManager->addUserActivity(UserActivity::DELETED, 'client', $clientId);
            }

            return $deleted;
        }

        throw new ForbiddenException('Not allowed to delete client.');
    }
}
