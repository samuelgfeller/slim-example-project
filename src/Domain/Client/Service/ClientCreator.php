<?php


namespace App\Domain\Client\Service;


use App\Domain\Client\Data\ClientData;
use App\Domain\Exceptions\ForbiddenException;
use App\Infrastructure\Authentication\UserRoleFinderRepository;
use App\Infrastructure\Client\ClientCreatorRepository;

class ClientCreator
{

    public function __construct(
        private readonly ClientValidator $clientValidator,
        private readonly ClientCreatorRepository $clientCreatorRepository,
        private readonly UserRoleFinderRepository $userRoleFinderRepository
    ) {
    }

    /**
     * Validate user input values and client
     *
     * @param int $loggedInUserId
     * @return int insert id
     */
    public function createClient(array $clientValues, int $loggedInUserId): int
    {
        $client = new ClientData($clientValues);
        $this->clientValidator->validateClientCreation($client, $clientValues['birthdate'] ?? null);

        $userRole = $this->userRoleFinderRepository->getUserRoleById($loggedInUserId);
        // All users can create clients
        if ($userRole === 'admin' || $userRole === 'user') {
            return $this->clientCreatorRepository->insertClient($client->toArrayForDatabase());
        }
        // User does not have needed rights to access area or function
        // $this->logger->notice(
        //     'User ' . $loggedInUserId . ' tried to create client but isn\'t allowed.'
        // );
        throw new ForbiddenException('Not allowed to create client.');
    }
}
