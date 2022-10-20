<?php


namespace App\Domain\Client\Service;


use App\Domain\Client\Authorization\ClientAuthorizationChecker;
use App\Domain\Client\Data\ClientData;
use App\Domain\Exceptions\ForbiddenException;
use App\Infrastructure\Client\ClientCreatorRepository;

class ClientCreator
{

    public function __construct(
        private readonly ClientValidator $clientValidator,
        private readonly ClientCreatorRepository $clientCreatorRepository,
        private readonly ClientAuthorizationChecker $clientAuthorizationChecker,
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
        $this->clientValidator->validateClientCreation($client, $clientValues['birthdate'] ?? null);

        if ($this->clientAuthorizationChecker->isGrantedToCreateClient($client)) {
            return $this->clientCreatorRepository->insertClient($client->toArrayForDatabase());
        }

        throw new ForbiddenException('Not allowed to create client.');
    }
}
