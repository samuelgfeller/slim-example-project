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
        private readonly ClientUpdaterRepository $clientUpdaterRepository,
        private readonly UserRoleFinderRepository $userRoleFinderRepository,
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
        $this->clientValidator->validateClientUpdate($client, $clientValues['birthdate'] ?? null);

        // Find note in db to compare its ownership
        $clientFromDb = $this->clientFinder->findClient($clientId);

        // I write the role logic always for each function and not a general service "isAuthorised" function because it's too different every time
        $userRole = $this->userRoleFinderRepository->getUserRoleById($loggedInUserId);
        // Check if it's admin or if it's its own client or user as all users should be able to update all clients
        if ($userRole === 'admin' || $clientFromDb->userId === $loggedInUserId || $userRole === 'user') {
            $updateData = [];
            // To be sure that only the wanted column is updated it is added to the $updateData if set
            if (null !== $client->clientStatusId) {
                $updateData['client_status_id'] = $client->clientStatusId;
            }
            if (null !== $client->userId) {
                $updateData['user_id'] = $client->userId;
            }
            if (null !== $client->firstName) {
                $updateData['first_name'] = $client->firstName;
            }
            if (null !== $client->lastName) {
                $updateData['last_name'] = $client->lastName;
            }
            if (null !== $client->phone) {
                $updateData['phone'] = $client->phone;
            }
            if (null !== $client->location) {
                $updateData['location'] = $client->location;
            }
            if (null !== $client->birthdate) {
                $updateData['birthdate'] = $client->birthdate->format('Y-m-d');
            }
            if (null !== $client->email) {
                $updateData['email'] = $client->email;
            }
            if (null !== $client->sex) {
                $updateData['sex'] = $client->sex;
            }

            return $this->clientUpdaterRepository->updateClient($updateData, $clientId);
        }
        // User does not have needed rights to access area or function
        $this->logger->notice(
            'User ' . $loggedInUserId . ' tried to update client with id: ' . $clientId .
            ' but isn\'t allowed.'
        );
        throw new ForbiddenException('Not allowed to change that client.');
    }


}