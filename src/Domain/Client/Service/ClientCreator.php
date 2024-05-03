<?php

namespace App\Domain\Client\Service;

use App\Application\Data\UserNetworkSessionData;
use App\Domain\Authentication\Exception\ForbiddenException;
use App\Domain\Client\Data\ClientData;
use App\Domain\Client\Repository\ClientCreatorRepository;
use App\Domain\Client\Repository\ClientDeleterRepository;
use App\Domain\Client\Service\Authorization\ClientPermissionVerifier;
use App\Domain\Exception\ValidationException;
use App\Domain\Note\Service\NoteCreator;
use App\Domain\User\Enum\UserActivity;
use App\Domain\UserActivity\Service\UserActivityLogger;

final readonly class ClientCreator
{
    public function __construct(
        private ClientValidator $clientValidator,
        private ClientCreatorRepository $clientCreatorRepository,
        private ClientPermissionVerifier $clientPermissionVerifier,
        private NoteCreator $noteCreator,
        private ClientDeleterRepository $clientDeleterRepository,
        private UserActivityLogger $userActivityLogger,
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

        if ($this->clientPermissionVerifier->isGrantedToCreate($client)) {
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
                $this->userActivityLogger->deleteUserActivity($clientInsertActivityId);
                // Throw exception for it to be caught in middleware
                throw $validationException;
            }
        }

        throw new ForbiddenException('Not allowed to create client.');
    }
}
