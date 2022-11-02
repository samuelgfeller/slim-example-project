<?php


namespace App\Domain\Client\Service;


use App\Domain\Client\Authorization\ClientAuthorizationChecker;
use App\Domain\Client\Data\ClientData;
use App\Domain\Exceptions\ForbiddenException;
use App\Domain\Exceptions\ValidationException;
use App\Domain\Note\Service\NoteCreator;
use App\Infrastructure\Client\ClientCreatorRepository;
use App\Infrastructure\Client\ClientDeleterRepository;

class ClientCreator
{

    public function __construct(
        private readonly ClientValidator $clientValidator,
        private readonly ClientCreatorRepository $clientCreatorRepository,
        private readonly ClientAuthorizationChecker $clientAuthorizationChecker,
        private readonly NoteCreator $noteCreator,
        private readonly ClientDeleterRepository $clientDeleterRepository,
    ) {
    }

    /**
     * Validate user input values and client
     *
     * @return int insert id
     * @throws ForbiddenException
     */
    public function createClient(array $clientValues): int
    {
        $client = new ClientData($clientValues);
        // Validate entire client object resulting of user input values excluding main note
        $this->clientValidator->validateClientCreation($client, $clientValues['birthdate'] ?? null);

        if ($this->clientAuthorizationChecker->isGrantedToCreate($client)) {
            $clientId = $this->clientCreatorRepository->insertClient($client->toArrayForDatabase());
            // Create main note
            try {
                if (!empty($clientValues['message'])) {
                    // Create main note if message is not empty
                    $this->noteCreator->createNote([
                        'message' => $clientValues['message'],
                        'client_id' => $clientId,
                        'user_id' => $client->userId,
                        'is_main' => 1
                    ]);
                }
                return $clientId;
            } catch (ValidationException $validationException){
                // Main note creation wasn't successful so user has to adapt form and will re-submit all client data
                // and to prevent duplicate the newly created client has to be deleted
                $this->clientDeleterRepository->hardDeleteClient($clientId);
                throw $validationException;
            }
        }

        throw new ForbiddenException('Not allowed to create client.');
    }
}
