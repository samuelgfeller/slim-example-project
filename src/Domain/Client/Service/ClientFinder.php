<?php


namespace App\Domain\Client\Service;


use App\Domain\Authentication\Exception\ForbiddenException;
use App\Domain\Authorization\Privilege;
use App\Domain\Client\Authorization\ClientAuthorizationChecker;
use App\Domain\Client\Authorization\ClientAuthorizationGetter;
use App\Domain\Client\Data\ClientData;
use App\Domain\Client\Data\ClientResultAggregateData;
use App\Domain\Client\Data\ClientResultDataCollection;
use App\Domain\Note\Authorization\NoteAuthorizationChecker;
use App\Domain\Note\Authorization\NoteAuthorizationGetter;
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
        private readonly ClientAuthorizationGetter $clientAuthorizationGetter,
        private readonly NoteAuthorizationGetter $noteAuthorizationGetter,
        private readonly NoteAuthorizationChecker $noteAuthorizationChecker,
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
            if ($value === null) {
                $is = ' IS'; // To be added right after column
            }
            $queryBuilderWhereArray["client.$column$is"] = $value;
        }

        $clientResultCollection = new ClientResultDataCollection();
        // Retrieve clients
        $clientResultCollection->clients = $this->findClientsWhereWithResultAggregate(
            $queryBuilderWhereArray
        );

        $clientResultCollection->statuses = $this->clientStatusFinderRepository->findAllClientStatusesForDropdown();
        $clientResultCollection->users = $this->userNameAbbreviator->abbreviateUserNamesForDropdown(
            $this->userFinderRepository->findAllUsers()
        );

        // Add permissions on what logged-in user is allowed to do with object
//        $this->clientUserRightSetter->defineUserRightsOnClients($allClientResults);
        return $clientResultCollection;
    }

    /**
     * Finds and adds user_id change and client_status_id change privilege
     * to found clientResultAggregate filtered by the given $whereArray
     *
     * @param array $whereArray cake query builder where array -> ['table.field' => 'value']
     * @return ClientResultAggregateData[]
     */
    private function findClientsWhereWithResultAggregate(array $whereArray = ['client.deleted_at IS' => null]): array
    {
        $clientResultsWithAggregates = $this->clientFinderRepository->findClientsWithResultAggregate($whereArray);
        // Add assigned user and client status privilege to each clientResultAggregate
        foreach ($clientResultsWithAggregates as $client) {
            $client->assignedUserPrivilege = $this->clientAuthorizationGetter->getMutationPrivilegeForClientColumn(
                $client->userId,
                'user_id'
            );
            //  Set client status privilege
            $client->clientStatusPrivilege = $this->clientAuthorizationGetter->getMutationPrivilegeForClientColumn(
                $client->userId,
                'client_status_id',
            );
        }
        return $clientResultsWithAggregates;
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
        if ($clientResultAggregate->id &&
            $this->clientAuthorizationChecker->isGrantedToRead($clientResultAggregate->userId)
        ) {
            // Set client mutation privilege
            $clientResultAggregate->mainDataPrivilege = $this->clientAuthorizationGetter->getMutationPrivilegeForClientColumn(
                $clientResultAggregate->userId,
                'main_data'
            );
            // Set main note privilege
            $clientResultAggregate->mainNoteData->privilege = $this->noteAuthorizationGetter->getMainNotePrivilege(
                $clientResultAggregate->mainNoteData->userId,
                $clientResultAggregate->userId
            );

            // Set assigned user privilege
            $clientResultAggregate->assignedUserPrivilege = $this->clientAuthorizationGetter->getMutationPrivilegeForClientColumn(
                $clientResultAggregate->userId,
                'user_id',
            );
            //  Set client status privilege
            $clientResultAggregate->clientStatusPrivilege = $this->clientAuthorizationGetter->getMutationPrivilegeForClientColumn(
                $clientResultAggregate->userId,
                'client_status_id',
            );
            //  Set create note privilege
            $clientResultAggregate->noteCreatePrivilege = $this->noteAuthorizationChecker->isGrantedToCreate(
                0,
                $clientResultAggregate->userId,
                false
            ) ? Privilege::CREATE : Privilege::NONE;

            if ($includingNotes === true) {
                $clientResultAggregate->notes = $this->noteFinder->findAllNotesFromClientExceptMain(
                    $clientId
                );
            } else {
                $clientResultAggregate->notesAmount = $this->noteFinder->findClientNotesAmount($clientId);
            }
            return $clientResultAggregate;
        }
        // The reasons this exception is thrown when tried to access soft deleted clients:
        // they are supposed to be deleted so only maybe a very high privileged role should have access, and it should
        // clearly be marked as deleted in the GUI as well. Also, a non-authorized user that is trying to access a client
        // should not be able to distinguish which clients exist and which not so for both cases the not allowed exception
        throw new ForbiddenException('Not allowed to read client.');
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