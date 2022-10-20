<?php


namespace App\Domain\Client\Service;


use App\Domain\Client\Authorization\ClientAuthorizationChecker;
use App\Domain\Client\Data\ClientData;
use App\Domain\Client\Data\ClientDropdownValuesData;
use App\Domain\Client\Data\ClientResultAggregateData;
use App\Domain\Client\Data\ClientResultDataCollection;
use App\Domain\Note\Service\NoteFinder;
use App\Domain\User\Service\UserNameAbbreviator;
use App\Infrastructure\Client\ClientFinderRepository;
use App\Infrastructure\Client\ClientStatus\ClientStatusFinderRepository;
use App\Infrastructure\User\UserFinderRepository;

class ClientFinder
{
    public function __construct(
        private readonly ClientFinderRepository $clientFinderRepository,
        private readonly UserFinderRepository $userFinderRepository,
        private readonly UserNameAbbreviator $userNameAbbreviator,
        private readonly ClientStatusFinderRepository $clientStatusFinderRepository,
        private readonly NoteFinder $noteFinder,
        private readonly ClientAuthorizationChecker $clientAuthorizationChecker,
    ) {
    }

    /**
     * Gives clients from db with aggregate data
     * matching given filter params
     *
     * @param array $filterParams default deleted_at null
     * @return ClientResultDataCollection
     */
    public function findClientsWithAggregates(array $filterParams = ['deleted_at' => null]): ClientResultDataCollection
    {
        // Build where array for cakephp query builder
        $queryBuilderWhereArray = [];
        foreach ($filterParams as $column => $value) {
            // If expected value is "null" the word "IS" is needed in the array key right after the column
            $is = '';
            if ($value === null){
                $is = ' IS'; // To be added right after column
            }
            $queryBuilderWhereArray["client.$column$is"] = $value;
        }

        $clientResultCollection = new ClientResultDataCollection();
        $clientResultCollection->clients = $this->clientFinderRepository->findClientsWithResultAggregate($queryBuilderWhereArray);
        $clientResultCollection->statuses = $this->clientStatusFinderRepository->findAllStatusesForDropdown();
        $clientResultCollection->users = $this->userNameAbbreviator->abbreviateUserNamesForDropdown(
            $this->userFinderRepository->findAllUsers()
        );

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
        if ($this->clientAuthorizationChecker->isGrantedToReadClient($clientResultAggregate->userId)) {
            if ($includingNotes === true) {
                $clientResultAggregate->notes = $this->noteFinder->findAllNotesFromClientExceptMain($clientId);
            } else {
                $clientResultAggregate->notesAmount = $this->noteFinder->findClientNotesAmount($clientId);
            }
            return $clientResultAggregate;
        }
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
            $this->userNameAbbreviator->abbreviateUserNamesForDropdown($this->userFinderRepository->findAllUsers()),
        );
    }

}