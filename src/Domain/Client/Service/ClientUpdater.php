<?php


namespace App\Domain\Client\Service;


use App\Domain\Exceptions\ForbiddenException;
use App\Domain\Factory\LoggerFactory;
use App\Domain\Client\Data\ClientData;
use App\Infrastructure\Authentication\UserRoleFinderRepository;
use App\Infrastructure\Client\ClientUpdaterRepository;
use Psr\Log\LoggerInterface;

class ClientUpdater
{
    private LoggerInterface $logger;

    public function __construct(
        private ClientValidator $postValidator,
        private ClientUpdaterRepository $clientUpdaterRepository,
        private UserRoleFinderRepository $userRoleFinderRepository,
        private readonly ClientValidator $clientValidator,
        private readonly ClientFinder $clientFinder,
        LoggerFactory $logger

    ) {
        $this->logger = $logger->addFileHandler('error.log')->createInstance('post-service');
    }

    /**
     * Change something or multiple things on post
     *
     * @param int $clientId id of post being changed
     * @param array|null $clientValues values that have to be changed
     * @param int $loggedInUserId
     * @return bool if update was successful
     */
    public function updateClient(int $clientId, null|array $clientValues, int $loggedInUserId): bool
    {
        // Init object for validation
        $client = new ClientData($clientValues);
        $this->clientValidator->validateClientUpdate($client);

        // Find note in db to compare its ownership
        $clientFromDb = $this->clientFinder->findClient($clientId);

        // I write the role logic always for each function and not a general service "isAuthorised" function because it's too different every time
        $userRole = $this->userRoleFinderRepository->getUserRoleById($loggedInUserId);
        // Check if it's admin or if it's its own client or user as all users should be able to update all clients
        if ($userRole === 'admin' || $clientFromDb->user_id === $loggedInUserId || $userRole === 'user') {
            $updateData = [];
            if (null !== $client->client_status_id) {
                // To be sure that only the message will be updated
                $updateData['client_status_id'] = $client->client_status_id;
            }
            if (null !== $client->user_id) {
                // To be sure that only the message will be updated
                $updateData['user_id'] = $client->user_id;
            }

            return $this->clientUpdaterRepository->updateClient($updateData, $clientId);
        }
        // User does not have needed rights to access area or function
        $this->logger->notice(
            'User ' . $loggedInUserId . ' tried to update client with id: ' . $loggedInUserId .
            ' but isn\'t allowed.'
        );
        throw new ForbiddenException('Not allowed to change that client as it\'s linked to another user.');
    }


}