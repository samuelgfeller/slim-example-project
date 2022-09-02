<?php

namespace App\Domain\Client\Service;

use App\Domain\Exceptions\ForbiddenException;
use App\Domain\Factory\LoggerFactory;
use App\Infrastructure\Authentication\UserRoleFinderRepository;
use App\Infrastructure\Client\ClientStatus\ClientStatusUpdaterRepository;
use Psr\Log\LoggerInterface;

class ClientStatusUpdater
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly ClientStatusUpdaterRepository $clientStatusUpdaterRepository,
        private readonly UserRoleFinderRepository $userRoleFinderRepository,
        private readonly ClientFinder $clientFinder,
        LoggerFactory $logger

    ) {
        $this->logger = $logger->addFileHandler('error.log')->createInstance('client-service');
    }

    /**
     * Change client status id
     *
     * @param int $clientId
     * @param int $newStatusId
     * @return bool
     */
    public function changeClientStatus(int $clientId, int $newStatusId, int $loggedInUserId): bool
    {
        // Modify the actual client status entry for admins

        // I write the role logic always for each function and not a general service "isAuthorised" function because it's too different every time
        $userRole = $this->userRoleFinderRepository->getUserRoleById($loggedInUserId);
        // Check if it's admin or if it's its own client or user as all users should be able to update all clients
        if ($userRole === 'admin') {
            return $this->clientStatusUpdaterRepository->changeClientStatus($clientId, ['client_id' => $newStatusId]);
        }
        // User does not have needed rights to access area or function
        $this->logger->notice(
            'User ' . $loggedInUserId . ' tried to change client status of client '. $clientId.
            ' with status id: ' . $newStatusId
        );
        throw new ForbiddenException('Not allowed to change that note.');
    }
}