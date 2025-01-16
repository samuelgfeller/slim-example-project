<?php

namespace App\Module\Client\Create\Service;

use App\Core\Application\Data\UserNetworkSessionData;
use App\Module\Authorization\Exception\ForbiddenException;
use App\Module\Client\Create\Repository\ClientCreatorRepository;
use App\Module\Client\Data\ClientData;
use App\Module\Client\Delete\Repository\ClientDeleterRepository;
use App\Module\Client\Validation\Service\ClientValidator;
use App\Module\Note\Create\Service\NoteCreator;
use App\Module\User\Enum\UserActivity;
use App\Module\UserActivity\Create\Service\UserActivityLogger;
use App\Module\UserActivity\Delete\Service\UserActivityDeleter;
use App\Module\Validation\ValidationException;

final readonly class ClientCreator
{
    public function __construct(
        private ClientValidator $clientValidator,
        private ClientCreatorRepository $clientCreatorRepository,
        private ClientCreateAuthorizationChecker $clientCreateAuthorizationChecker,
        private NoteCreator $noteCreator,
        private ClientDeleterRepository $clientDeleterRepository,
        private UserActivityLogger $userActivityLogger,
        private UserActivityDeleter $userActivityDeleter,
        private UserNetworkSessionData $userNetworkSessionData,
    ) {
    }

    /**
     * Validate input values and create client if authorized.
     *
     * @param array $clientValues
     *
     * @throws ForbiddenException
     *
     * @return int insert id
     */
    public function createClient(array $clientValues): int
    {
        $this->clientValidator->validateClientValues($clientValues, true);

        $client = new ClientData($clientValues);

        if ($this->clientCreateAuthorizationChecker->isGrantedToCreate($client)) {
            // Insert client
            $clientId = $this->clientCreatorRepository->insertClient($client->toArrayForDatabase());
            // Insert user activity
            $clientInsertActivityId = $this->userActivityLogger->logUserActivity(
                UserActivity::CREATED,
                'client',
                $clientId,
                $client->toArrayForDatabase()
            );
            // Create main note
            try {
                if (!empty($clientValues['message'])) {
                    // Create main note if the message is not empty
                    $mainNoteValues = [
                        'message' => $clientValues['message'],
                        'client_id' => $clientId,
                        'user_id' => $this->userNetworkSessionData->userId,
                        'is_main' => 1,
                    ];
                    $this->noteCreator->createNote($mainNoteValues);
                }

                return $clientId;
            } catch (ValidationException $validationException) {
                // Main note creation wasn't successful, so the user has to adapt form and will re-submit all
                // client data and to prevent duplicate, the newly created client has to be deleted.
                $this->clientDeleterRepository->hardDeleteClient($clientId);
                // Also remove client creation activity
                $this->userActivityDeleter->deleteUserActivity($clientInsertActivityId);
                // Throw exception for it to be caught in middleware
                throw $validationException;
            }
        }

        throw new ForbiddenException('Not allowed to create client.');
    }
}
