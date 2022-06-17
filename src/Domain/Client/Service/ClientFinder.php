<?php


namespace App\Domain\Client\Service;


use App\Domain\Client\Data\ClientData;
use App\Infrastructure\Client\ClientFinderRepository;

class ClientFinder
{
    public function __construct(
        private readonly ClientFinderRepository  $clientFinderRepository,
        private readonly ClientUserRightSetter $clientUserRightSetter,
    ) {
    }

    /**
     * Gives all undeleted clients from db with aggregate data
     *
     * @return ClientData[]
     */
    public function findAllClientsWithAggregate(): array
    {
        $allClientResults = $this->clientFinderRepository->findAllClientsWithResultAggregate();

        // Add permissions on what logged-in user is allowed to do with object
        $this->clientUserRightSetter->defineUserRightsOnClients($allClientResults);
        return $allClientResults;
    }

    /**
     * Find one client in the database
     *
     * @param $id
     * @return ClientData
     */
    public function findClient($id): ClientData
    {
        return $this->clientFinderRepository->findClientById($id);
    }

    /**
     * Find one client in the database with aggregate
     *
     * @param $id
     * @return ClientData
     */
    public function findClientAggregate($id): ClientData
    {
        return $this->clientFinderRepository->findClientAggregateById($id);
    }


    /**
     * Return all posts which are linked to the given user
     *
     * @param int $userId
     * @return ClientData[]
     */
    public function findAllClientsFromUser(int $userId): array
    {
        $allClients = $this->clientFinderRepository->findAllClientsByUserId($userId);
        $this->clientUserRightSetter->defineUserRightsOnClients($allClients);
        return $allClients;
    }

}