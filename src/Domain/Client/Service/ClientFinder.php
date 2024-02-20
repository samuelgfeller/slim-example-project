<?php

namespace App\Domain\Client\Service;

use App\Domain\Authentication\Exception\ForbiddenException;
use App\Domain\Authorization\Privilege;
use App\Domain\Client\Data\ClientData;
use App\Domain\Client\Data\ClientListResult;
use App\Domain\Client\Data\ClientListResultCollection;
use App\Domain\Client\Data\ClientReadResult;
use App\Domain\Client\Repository\ClientFinderRepository;
use App\Domain\Client\Repository\ClientStatus\ClientStatusFinderRepository;
use App\Domain\Client\Service\Authorization\ClientPermissionVerifier;
use App\Domain\Client\Service\Authorization\ClientPrivilegeDeterminer;
use App\Domain\Note\Repository\NoteFinderRepository;
use App\Domain\Note\Service\Authorization\NotePermissionVerifier;
use App\Domain\Note\Service\Authorization\NotePrivilegeDeterminer;
use App\Domain\User\Repository\UserFinderRepository;
use App\Domain\User\Service\UserNameAbbreviator;

final readonly class ClientFinder
{
    public function __construct(
        private ClientFinderRepository $clientFinderRepository,
        private UserFinderRepository $userFinderRepository,
        private UserNameAbbreviator $userNameAbbreviator,
        private ClientStatusFinderRepository $clientStatusFinderRepository,
        private NoteFinderRepository $noteFinderRepository,
        private ClientPermissionVerifier $clientPermissionVerifier,
        private ClientPrivilegeDeterminer $clientPrivilegeDeterminer,
        private NotePrivilegeDeterminer $notePrivilegeDeterminer,
        private NotePermissionVerifier $notePermissionVerifier,
    ) {
    }

    /**
     * Gives clients from db with aggregate data
     * matching given filter params (client list).
     *
     * @param array $queryBuilderWhereArray
     *
     * @return ClientListResultCollection
     */
    public function findClientListWithAggregates(array $queryBuilderWhereArray): ClientListResultCollection
    {
        $clientResultCollection = new ClientListResultCollection();
        // Retrieve clients
        $clientResultCollection->clients = $this->findClientsWhereWithResultAggregate($queryBuilderWhereArray);

        $clientResultCollection->statuses = $this->clientStatusFinderRepository->findAllClientStatusesMappedByIdName();
        $clientResultCollection->users = $this->userNameAbbreviator->abbreviateUserNames(
            $this->userFinderRepository->findAllUsers()
        );

        // Add permissions on what logged-in user is allowed to do with object
        return $clientResultCollection;
    }

    /**
     * Finds and adds user_id change and client_status_id change privilege
     * to found clientResultAggregate filtered by the given $whereArray.
     *
     * @param array $whereArray cake query builder where array -> ['table.field' => 'value']
     *
     * @return ClientListResult[]
     */
    private function findClientsWhereWithResultAggregate(array $whereArray = ['client.deleted_at IS' => null]): array
    {
        $clientResultsWithAggregates = $this->clientFinderRepository->findClientsWithResultAggregate($whereArray);
        // Add assigned user and client status privilege to each clientResultAggregate
        foreach ($clientResultsWithAggregates as $key => $client) {
            if ($this->clientPermissionVerifier->isGrantedToRead($client->userId, $client->deletedAt)) {
                $client->assignedUserPrivilege = $this->clientPrivilegeDeterminer->getMutationPrivilege(
                    $client->userId,
                    'user_id'
                );
                //  Set client status privilege
                $client->clientStatusPrivilege = $this->clientPrivilegeDeterminer->getMutationPrivilege(
                    $client->userId,
                    'client_status_id',
                );
            } else {
                unset($clientResultsWithAggregates[$key]);
            }
        }

        return $clientResultsWithAggregates;
    }

    /**
     * Find one client in the database.
     *
     * @param int $id
     * @param mixed $includeDeleted
     *
     * @return ClientData
     */
    public function findClient(int $id, $includeDeleted = false): ClientData
    {
        return $this->clientFinderRepository->findClientById($id, $includeDeleted);
    }

    /**
     * Find one client in the database with aggregate.
     *
     * @param int $clientId
     *
     * @return ClientReadResult
     */
    public function findClientReadAggregate(int $clientId): ClientReadResult
    {
        $clientResultAggregate = $this->clientFinderRepository->findClientAggregateByIdIncludingDeleted($clientId);
        if ($clientResultAggregate->id
            && $this->clientPermissionVerifier->isGrantedToRead(
                $clientResultAggregate->userId,
                $clientResultAggregate->deletedAt
            )
        ) {
            // Set client mutation privilege
            $clientResultAggregate->generalPrivilege = $this->clientPrivilegeDeterminer->getMutationPrivilege(
                $clientResultAggregate->userId,
                'personal_info'
            );
            // Set main note privilege
            if ($clientResultAggregate->mainNoteData !== null) {
                $clientResultAggregate->mainNoteData->privilege = $this->notePrivilegeDeterminer->getMainNotePrivilege(
                    $clientResultAggregate->mainNoteData->userId,
                    $clientResultAggregate->userId
                );
            }

            // Set assigned user privilege
            $clientResultAggregate->assignedUserPrivilege = $this->clientPrivilegeDeterminer->getMutationPrivilege(
                $clientResultAggregate->userId,
                'user_id',
            );
            //  Set client status privilege
            $clientResultAggregate->clientStatusPrivilege = $this->clientPrivilegeDeterminer->getMutationPrivilege(
                $clientResultAggregate->userId,
                'client_status_id',
            );
            //  Set create note privilege
            $clientResultAggregate->noteCreatePrivilege = $this->notePermissionVerifier->isGrantedToCreate(
                0,
                $clientResultAggregate->userId,
                false
            ) ? Privilege::CR->name : Privilege::N->name;

            $clientResultAggregate->notesAmount = $this->noteFinderRepository->findClientNotesAmount($clientId);

            return $clientResultAggregate;
        }
        // The reasons this exception is thrown when tried to access soft deleted clients:
        // they are supposed to be deleted, so only maybe a very high privileged role should have access, and it should
        // clearly be marked as deleted in the GUI as well. Also, a non-authorized user trying to access a client
        // should not be able to distinguish which clients exist and which not so for both cases the not allowed exception
        throw new ForbiddenException('Not allowed to read client.');
    }
}
