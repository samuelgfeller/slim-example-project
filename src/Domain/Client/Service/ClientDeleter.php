<?php


namespace App\Domain\Client\Service;


use App\Domain\Client\Authorization\ClientAuthorizationChecker;
use App\Domain\Exceptions\ForbiddenException;
use App\Infrastructure\Client\ClientDeleterRepository;

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

        if ($this->clientAuthorizationChecker->isGrantedToDelete($clientFromDb->userId)) {
            return $this->clientDeleterRepository->deleteClient($clientId);
        }

        throw new ForbiddenException('Not allowed to delete client.');
    }
}