<?php

namespace App\Module\Client\List\Domain\Service;

use App\Module\Client\Authorization\Service\ClientPermissionVerifier;
use App\Module\Client\Authorization\Service\ClientPrivilegeDeterminer;
use App\Module\Client\ClientStatus\Repository\ClientStatusFinderRepository;
use App\Module\Client\List\Data\ClientListResult;
use App\Module\Client\List\Data\ClientListResultCollection;
use App\Module\Client\List\Repository\ClientListFinderRepository;
use App\Module\User\Repository\UserFinderRepository;
use App\Module\User\Service\UserNameAbbreviator;

final readonly class ClientListFinder
{
    public function __construct(
        private ClientListFinderRepository $clientListFinderRepository,
        private UserFinderRepository $userFinderRepository,
        private UserNameAbbreviator $userNameAbbreviator,
        private ClientStatusFinderRepository $clientStatusFinderRepository,
        private ClientPermissionVerifier $clientPermissionVerifier,
        private ClientPrivilegeDeterminer $clientPrivilegeDeterminer,
    ) {
    }

    /**
     * Returns clients from db with aggregate data
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
        $clientResultsWithAggregates = $this->clientListFinderRepository->findClientsWithResultAggregate($whereArray);
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
}
