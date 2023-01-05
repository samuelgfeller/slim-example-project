<?php

namespace App\Domain\Client\Service;

use App\Domain\Client\Authorization\ClientAuthorizationChecker;
use App\Domain\Client\Data\ClientResultDataCollection;
use App\Domain\Client\Exception\InvalidClientFilterException;
use App\Domain\FilterSetting\FilterModule;
use App\Domain\FilterSetting\FilterSettingSaver;

class ClientFinderWithFilter
{
    public function __construct(
        private readonly ClientFinder $clientFinder,
        private readonly ClientFilterWhereConditionBuilder $clientFilterWhereConditionBuilder,
        private readonly ClientAuthorizationChecker $clientAuthorizationChecker,
        private readonly FilterSettingSaver $filterSettingSaver,
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
            // Remove deleted_at from filter params array
            unset($filterParams['deleted_at']);
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
        // Filter client by name
        if (isset($params['name'])) {
            $filterParams['name'] = $params['name'];
        }
        // Filter client by date at which it was assigned
        if (isset($params['recently-assigned'])) {
            // If value is 1 the default time range is taken which is 1 week
            if ((int)$params['recently-assigned'] === 1) {
                $date = new \DateTime('-1 week');
            } // If it's a valid date (validation source: https://stackoverflow.com/a/24401462/9013718), the date is taken
            elseif (strtotime((string)$params['recently-assigned'])) {
                $date = new \DateTimeImmutable($params['recently-assigned']);
            } else {
                throw new InvalidClientFilterException('Invalid filter format "recently-assigned".');
            }
            // Add to filter params if date greater than given date and user_id is not null
            $filterParams['assigned_at >'] = $date->format('Y-m-d H:i:s');
            $filterParams['user_id IS NOT'] = null;
        }

        // Other filters here

        // Insert filter ids into db
        if (isset($params['saveFilter']) && (int)$params['saveFilter'] === 1) {
            $this->filterSettingSaver->saveFilterSettingForAuthenticatedUser(
                $params['filterIds'] ?? null,
                FilterModule::CLIENT_LIST
            );
        }

        // Find all clients matching the filter regardless of logged-in user rights
        $queryBuilderWhereArray = $this->clientFilterWhereConditionBuilder->buildWhereArrayWithFilterParams(
            $filterParams
        );
        $clientResultDataCollection = $this->clientFinder->findClientListWithAggregates($queryBuilderWhereArray);
        // Remove clients that user is not allowed to see instead of throwing a ForbiddenException
        $clientResultDataCollection->clients = $this->clientAuthorizationChecker->removeNonAuthorizedClientsFromList(
            $clientResultDataCollection->clients
        );

        return $clientResultDataCollection;
    }
}
