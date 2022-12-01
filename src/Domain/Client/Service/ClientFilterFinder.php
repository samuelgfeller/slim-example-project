<?php


namespace App\Domain\Client\Service;


use App\Domain\Authorization\UnauthorizedException;
use App\Domain\Client\Authorization\ClientAuthorizationChecker;
use App\Domain\Client\Data\ClientResultDataCollection;
use App\Domain\Client\Exception\InvalidClientFilterException;
use Odan\Session\SessionInterface;

class ClientFilterFinder
{
    public function __construct(
        private readonly ClientFinder $clientFinder,
        private readonly SessionInterface $session,
        private readonly ClientAuthorizationChecker $clientAuthorizationChecker,
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
            // To display own posts, the client sends the filter user=session
            if ($params['user'] === 'session') {
                // User has to be logged-in to access own-posts
                if (($userId = $this->session->get('user_id')) !== null) {
                    $filterParams['user_id'] = $userId;
                } else {
                    throw new UnauthorizedException('You have to be logged in to access clients');
                }
            } // If user is a number
            elseif (is_numeric($params['user'])) {
                $filterParams['user_id'] = (int)$params['user'];
            } // If is 'empty' it means that the value should be null in the database
            elseif ($params['user'] === 'empty') {
                $filterParams['user_id'] = null;
            } // If not user 'session' and also not numeric neither 'empty'
            else {
                // Exception message in ClientListFilterProvider.php
                throw new InvalidClientFilterException('Invalid filter format "user".');
            }
        }
        // Filter: include deleted records
        if (isset($params['include-deleted']) && (int)$params['include-deleted'] === 1) {
            unset($filterParams['deleted_at']);
        }
        // Other filters here

        // Find all clients matching the filter regardless of logged-in user rights
        $clientResultDataCollection = $this->clientFinder->findClientsWithAggregates($filterParams);
        // Remove clients that user is not allowed to see instead of throwing a ForbiddenException
        $clientResultDataCollection->clients = $this->clientAuthorizationChecker->removeNonAuthorizedClientsFromList(
            $clientResultDataCollection->clients
        );
        return $clientResultDataCollection;
    }
}