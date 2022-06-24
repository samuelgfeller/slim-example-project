<?php


namespace App\Domain\Client\Service;


use App\Domain\Client\Data\ClientResultDataCollection;
use App\Domain\User\Service\UserNameAbbreviator;
use App\Infrastructure\Client\ClientFinderRepository;
use App\Infrastructure\Client\ClientStatus\ClientStatusFinderRepository;

class ClientFinder
{
    public function __construct(
        private readonly ClientFinderRepository  $clientFinderRepository,
        private readonly ClientUserRightSetter $clientUserRightSetter,
        private readonly UserNameAbbreviator $userNameAbbreviator,
        private readonly ClientStatusFinderRepository $clientStatusFinderRepository,
    ) {
    }

    /**
     * Gives all undeleted clients from db with aggregate data
     *
     * @return ClientResultDataCollection
     */
    public function findAllClientsWithAggregate(): ClientResultDataCollection
    {
        $clientResultCollection = new ClientResultDataCollection();
        $clientResultCollection->clients = $this->clientFinderRepository->findAllClientsWithResultAggregate();
        $clientResultCollection->statuses = $this->clientStatusFinderRepository->findAllStatusesForDropdown();
        $clientResultCollection->users = $this->userNameAbbreviator->findUserNamesForDropdown();

        // Add permissions on what logged-in user is allowed to do with object
//        $this->clientUserRightSetter->defineUserRightsOnClients($allClientResults);
        return $clientResultCollection;
    }

    /**
     * Find one client in the database
     *
     * @param $id
     * @return ClientDataAlias
     */
    public function findClient($id): ClientDataAlias
    {
        return $this->clientFinderRepository->findClientById($id);
    }

    /**
     * Find one client in the database with aggregate
     *
     * @param $id
     * @return ClientDataAlias
     */
    public function findClientAggregate($id): ClientDataAlias
    {
        return $this->clientFinderRepository->findClientAggregateById($id);
    }


    /**
     * Return all posts which are linked to the given user
     *
     * @param int $userId
     * @return ClientResultDataCollection
     */
    public function findAllClientsFromUser(int $userId): ClientResultDataCollection
    {
        $clientResultCollection = new ClientResultDataCollection();
        $clientResultCollection->clients = $this->clientFinderRepository->findAllClientsByUserId($userId);
//        $this->clientUserRightSetter->defineUserRightsOnClients($allClients);
        return $clientResultCollection;
    }

}