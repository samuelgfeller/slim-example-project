<?php


namespace App\Domain\Client\Service;


use App\Domain\Client\Authorization\ClientAuthorizationChecker;
use App\Domain\Exceptions\ForbiddenException;
use App\Domain\User\Enum\UserActivityAction;
use App\Domain\User\Service\UserActivityManager;
use App\Infrastructure\Client\ClientDeleterRepository;

class ClientDeleter
{
    public function __construct(
        private readonly ClientDeleterRepository $clientDeleterRepository,
        private readonly ClientFinder $clientFinder,
        private readonly ClientAuthorizationChecker $clientAuthorizationChecker,
        private readonly UserActivityManager $userActivityManager,
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
            $deleted = $this->clientDeleterRepository->deleteClient($clientId);
            if ($deleted) {
                $this->userActivityManager->addUserActivity(UserActivityAction::DELETED, 'client', $clientId);
            }
            return $deleted;
        }

        throw new ForbiddenException('Not allowed to delete client.');
    }
}