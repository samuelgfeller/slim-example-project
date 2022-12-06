<?php


namespace App\Domain\Client\Service;


use App\Domain\Client\Authorization\ClientAuthorizationChecker;
use App\Domain\Client\Data\ClientResultDataCollection;
use App\Domain\Client\Exception\InvalidClientFilterException;
use App\Domain\Client\Service\ClientListFilter\UserClientListFilterHandler;

class ClientFinderWithFilter
{
    public function __construct(
        private readonly ClientFinder $clientFinder,
        private readonly ClientAuthorizationChecker $clientAuthorizationChecker,
        private readonly UserClientListFilterHandler $userClientListFilterHandler,
    ) {
    }

    /**
     * Return clients matching given filter.
     * If there is no filter, all clients that are not deleted are returned.
     *
     * @param array $params GET parameters containing filter values
     *
     * @return ClientResultDataCollection
     */
    public function findClientsWithFilter(array $params): ClientResultDataCollection
    {
        // Filter param names and values that will be in the request are stored in the db `client_list_filter.get_param`
        // Filters can be cumulated, so they are all stored in this array and then the where condition is generated out of it
        $filterParams = ['deleted_at' => null]; // Default filter
        // Filter 'user'
        if (isset($params['user'])) {
            // User ids are numeric or empty string (will be translated to IS null in client finder) or array
            if (is_numeric($params['user']) || $params['user'] === '' || is_array($params['user'])) {
                $filterParams['user_id'] = $params['user'];
            } else {
                // Exception message in ClientListFilterProvider.php
                throw new InvalidClientFilterException('Invalid filter format "user".');
            }
        }
        // Filter: include deleted records
        if (isset($params['include-deleted']) && (int)$params['include-deleted'] === 1) {
            unset($filterParams['deleted_at']);
        }
        // Filter: deleted records
        if (isset($params['deleted']) && (int)$params['deleted'] === 1) {
            $filterParams['deleted_at IS NOT'] = null;
        }
        // Filter client 'status'
        if (isset($params['status'])) {
            if (is_numeric($params['status']) || $params['status'] === '' || is_array($params['status'])) {
                $filterParams['client_status_id'] = $params['status'];
            } else {
                // Exception message in ClientListFilterProvider.php
                throw new InvalidClientFilterException('Invalid filter format "status".');
            }
        }
        // Other filters here

        // Add filter ids to session
        $this->userClientListFilterHandler->setClientListFilterSettingForAuthenticatedUser($params['filterIds'] ?? null);

        // Find all clients matching the filter regardless of logged-in user rights
        $queryBuilderWhereArray = $this->clientFinder->buildWhereArrayWithFilterParams($filterParams);
        $clientResultDataCollection = $this->clientFinder->findClientsWithAggregates($queryBuilderWhereArray);
        // Remove clients that user is not allowed to see instead of throwing a ForbiddenException
        $clientResultDataCollection->clients = $this->clientAuthorizationChecker->removeNonAuthorizedClientsFromList(
            $clientResultDataCollection->clients
        );
        return $clientResultDataCollection;
    }
}