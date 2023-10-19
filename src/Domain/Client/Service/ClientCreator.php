<?php

namespace App\Domain\Client\Service;

use App\Domain\Authentication\Exception\ForbiddenException;
use App\Domain\Client\Authorization\ClientAuthorizationChecker;
use App\Domain\Client\Data\ClientData;
use App\Domain\Note\Service\NoteCreator;
use App\Domain\User\Enum\UserActivity;
use App\Domain\User\Service\UserActivityManager;
use App\Domain\Validation\ValidationException;
use App\Infrastructure\Client\ClientCreatorRepository;
use App\Infrastructure\Client\ClientDeleterRepository;
use Odan\Session\SessionInterface;

class ClientCreator
{
    public function __construct(
        private readonly ClientValidatorVanilla $clientValidator,
        private readonly ClientCreatorRepository $clientCreatorRepository,
        private readonly ClientAuthorizationChecker $clientAuthorizationChecker,
        private readonly NoteCreator $noteCreator,
        private readonly ClientDeleterRepository $clientDeleterRepository,
        private readonly UserActivityManager $userActivityManager,
        private readonly SessionInterface $session,
    ) {
    }

    /**
     * Validate user input values and client.
     *
     * @param array $clientValues
     *
     * @throws ForbiddenException|\JsonException
     *
     * @return int insert id
     */
    public function createClient(array $clientValues): int
    {
        // Validate entire client values before object creation to prevent exception on invalid data such as datetime
        $this->clientValidator->validateClientCreation($clientValues);

        $client = new ClientData($clientValues);

        if ($this->clientAuthorizationChecker->isGrantedToCreate($client)) {
            // Insert client
            $clientId = $this->clientCreatorRepository->insertClient($client->toArrayForDatabase());
            // Insert user activity
            $clientInsertActivityId = $this->userActivityManager->addUserActivity(
                UserActivity::CREATED,
                'client',
                $clientId,
                $client->toArrayForDatabase()
            );
            // Create main note
            try {
                if (!empty($clientValues['message'])) {
                    // Create main note if message is not empty
                    $mainNoteValues = [
                        'message' => $clientValues['message'],
                        'client_id' => $clientId,
                        'user_id' => $this->session->get('user_id'),
                        'is_main' => 1,
                    ];
                    $this->noteCreator->createNote($mainNoteValues);
                }

                return $clientId;
            } catch (ValidationException $validationException) {
                // Main note creation wasn't successful so user has to adapt form and will re-submit all client data
                // and to prevent duplicate the newly created client has to be deleted
                $this->clientDeleterRepository->hardDeleteClient($clientId);
                // Also remove client creation activity
                $this->userActivityManager->deleteUserActivity($clientInsertActivityId);
                throw $validationException;
            }
        }

        throw new ForbiddenException('Not allowed to create client.');
    }
}
