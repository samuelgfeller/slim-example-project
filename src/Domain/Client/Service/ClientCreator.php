<?php


namespace App\Domain\Client\Service;


use App\Domain\Client\Authorization\ClientAuthorizationChecker;
use App\Domain\Client\Data\ClientData;
use App\Domain\Exceptions\ForbiddenException;
use App\Domain\Note\Service\NoteCreator;
use App\Infrastructure\Client\ClientCreatorRepository;

class ClientCreator
{

    public function __construct(
        private readonly ClientValidator $clientValidator,
        private readonly ClientCreatorRepository $clientCreatorRepository,
        private readonly ClientAuthorizationChecker $clientAuthorizationChecker,
        private readonly NoteCreator $noteCreator,
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
            $this->noteCreator->createNote([
                'message' => $clientValues['main_note'],
                'client_id' => $clientId,
                'user_id' => $client->userId,
                'is_main' => 1
            ]);
            return $clientId;
        }

        throw new ForbiddenException('Not allowed to create client.');
    }
}
