<?php


namespace App\Domain\Client\Service;


use App\Domain\Client\Data\ClientData;
use App\Domain\Client\Data\ClientDropdownValuesData;
use App\Domain\Client\Data\ClientResultAggregateData;
use App\Domain\Client\Data\ClientResultDataCollection;
use App\Domain\Note\Service\NoteFinder;
use App\Domain\User\Service\UserNameAbbreviator;
use App\Infrastructure\Client\ClientFinderRepository;
use App\Infrastructure\Client\ClientStatus\ClientStatusFinderRepository;

class ClientFinder
{
    public function __construct(
        private readonly ClientFinderRepository  $clientFinderRepository,
        private readonly UserNameAbbreviator $userNameAbbreviator,
        private readonly ClientStatusFinderRepository $clientStatusFinderRepository,
        private readonly NoteFinder $noteFinder,
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
     * @return ClientData
     */
    public function findClient($id): ClientData
    {
        return $this->clientFinderRepository->findClientById($id);
    }

    /**
     * Find one client in the database with aggregate
     *
     * @param int $clientId
     * @param bool $includingNotes
     * @return ClientResultAggregateData
     */
    public function findClientReadAggregate(int $clientId, bool $includingNotes = true): ClientResultAggregateData
    {
        $clientResultAggregate = $this->clientFinderRepository->findClientAggregateById($clientId);
        if ($includingNotes === true) {
            $clientResultAggregate->notes = $this->noteFinder->findAllNotesFromClientExceptMain($clientId);
        } else {
            $clientResultAggregate->notesAmount = $this->noteFinder->findClientNotesAmount($clientId);
        }
        return $clientResultAggregate;
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

    /**
     * Find all dropdown values for a client
     *
     * @return ClientDropdownValuesData
     */
    public function findClientDropdownValues(): ClientDropdownValuesData
    {
        return new ClientDropdownValuesData(
            $this->clientStatusFinderRepository->findAllStatusesForDropdown(),
            $this->userNameAbbreviator->findUserNamesForDropdown(),
        );
    }

}