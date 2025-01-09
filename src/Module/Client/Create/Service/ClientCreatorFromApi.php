<?php

namespace App\Module\Client\Create\Service;

use App\Module\Client\ClientStatus\Enum\ClientStatus;
use App\Module\Client\ClientStatus\Repository\ClientStatusFinderRepository;
use App\Module\Client\Create\Repository\ClientCreatorRepository;
use App\Module\Client\Validation\ClientValidator;
use App\Module\User\Enum\UserActivity;
use App\Module\UserActivity\Create\Service\UserActivityLogger;

final readonly class ClientCreatorFromApi
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
    public function createClientFromApi(array $clientValues): int
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
