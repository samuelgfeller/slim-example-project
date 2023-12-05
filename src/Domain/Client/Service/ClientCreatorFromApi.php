<?php

namespace App\Domain\Client\Service;

use App\Domain\Client\Enum\ClientStatus;
use App\Domain\Client\Repository\ClientCreatorRepository;
use App\Domain\Client\Repository\ClientStatus\ClientStatusFinderRepository;
use App\Domain\User\Enum\UserActivity;
use App\Domain\UserActivity\Service\UserActivityLogger;

readonly class ClientCreatorFromApi
{
    public function __construct(
        private ClientValidator $clientValidator,
        private ClientCreatorRepository $clientCreatorRepository,
        private UserActivityLogger $userActivityLogger,
        private ClientStatusFinderRepository $clientStatusFinderRepository,
    ) {
    }

    /**
     * Creates a client entry from the api call when the client submits
     * the form himself on the public front-page.
     *
     * @param array $clientValues
     *
     * @return int
     */
    public function createClientFromClientSubmit(array $clientValues): int
    {
        // Add default client status (action pending)
        $clientValues['client_status_id'] = $this->clientStatusFinderRepository->findClientStatusByName(
            ClientStatus::ACTION_PENDING
        );

        // Validate client object resulting of user input values excluding main note
        $this->clientValidator->validateClientValues($clientValues, true);

        // Insert client
        $clientId = $this->clientCreatorRepository->insertClient($clientValues);
        // Insert user activity
        $clientInsertActivityId = $this->userActivityLogger->logUserActivity(
            UserActivity::CREATED,
            'client',
            $clientId,
            $clientValues,
        );

        return $clientId;
    }
}
