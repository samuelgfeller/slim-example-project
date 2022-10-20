<?php


namespace App\Domain\Client\Service;


use App\Domain\Client\Authorization\ClientAuthorizationChecker;
use App\Domain\Exceptions\ForbiddenException;
use App\Infrastructure\Authentication\UserRoleFinderRepository;
use App\Infrastructure\Client\ClientDeleterRepository;
use App\Infrastructure\Note\NoteDeleterRepository;

class ClientDeleter
{
    public function __construct(
        private readonly ClientDeleterRepository $clientDeleterRepository,
        private readonly ClientFinder $clientFinder,
        private readonly ClientAuthorizationChecker $clientAuthorizationChecker,
    ) {
    }

    /**
     * Delete one post logic
     *
     * @param int $clientId
     * @return bool
     * @throws ForbiddenException
     */
    public function deleteClient(int $clientId): bool
    {
        // Find post in db to get its ownership
        $clientFromDb = $this->clientFinder->findClient($clientId);

        if ($this->clientAuthorizationChecker->isGrantedToDeleteClient($clientFromDb->userId)) {
            return $this->clientDeleterRepository->deleteClient($clientId);
        }

        throw new ForbiddenException('Not allowed to delete client.');
    }
}