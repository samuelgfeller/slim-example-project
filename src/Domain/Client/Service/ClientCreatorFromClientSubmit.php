<?php

namespace App\Domain\Client\Service;

use App\Domain\Client\Data\ClientData;
use App\Domain\Client\Enum\ClientStatus;
use App\Domain\User\Enum\UserActivity;
use App\Domain\User\Service\UserActivityManager;
use App\Infrastructure\Client\ClientCreatorRepository;
use App\Infrastructure\Client\ClientStatus\ClientStatusFinderRepository;

class ClientCreatorFromClientSubmit
{
    public function __construct(
        private readonly ClientValidator $clientValidator,
        private readonly ClientCreatorRepository $clientCreatorRepository,
        private readonly UserActivityManager $userActivityManager,
        private readonly ClientStatusFinderRepository $clientStatusFinderRepository,
    ) {
    }

    /**
     * Creates a client entry from the api call when the client submits
     * the form himself on the public front-page.
     *
     * @param array $clientValues
     *
     * @throws \Exception
     *
     * @return int
     */
    public function createClientFromClientSubmit(array $clientValues): int
    {
        $client = new ClientData($clientValues);
        // Add default client status (action pending)
        $client->clientStatusId = $this->clientStatusFinderRepository->findClientStatusByName(
            ClientStatus::ACTION_PENDING
        );

        // Validate client object resulting of user input values excluding main note
        $this->clientValidator->validateClientCreation($client, $clientValues['birthdate'] ?? null);

        // Insert client
        $clientId = $this->clientCreatorRepository->insertClient($client->toArrayForDatabase());
        // Insert user activity
        $clientInsertActivityId = $this->userActivityManager->addUserActivity(
            UserActivity::CREATED,
            'client',
            $clientId,
            $client->toArrayForDatabase(),
        );

        return $clientId;
    }
}
