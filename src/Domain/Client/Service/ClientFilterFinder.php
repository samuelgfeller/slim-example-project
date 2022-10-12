<?php


namespace App\Domain\Client\Service;


use App\Domain\Client\Data\ClientResultDataCollection;
use App\Domain\Client\Exception\InvalidClientFilterException;
use App\Domain\Exceptions\UnauthorizedException;
use Odan\Session\SessionInterface;

class ClientFilterFinder
{
    public function __construct(
        private readonly ClientFinder     $clientFinder,
        private readonly SessionInterface $session,
    )
    {
    }

    /**
     * Return posts matching given filter.
     * If there is no filter, all posts are returned.
     *
     * @param array $params GET parameters containing filter values
     *
     * @return ClientResultDataCollection
     */
    public function findClientsWithFilter(array $params): ClientResultDataCollection
    {
        // Filters can be cumulated, so they are all stored in this array and then the where condition is generated out of it
        $filterParams = ['deleted_at' => null]; // Default filter
        // Filter 'user'
        if (isset($params['user'])) {
            // To display own posts, the client sends the filter user=session
            if ($params['user'] === 'session'){
                // User has to be logged-in to access own-posts
                if(($userId = $this->session->get('user_id')) !== null){
                    $filterParams['user_id'] = $userId;
                } else {
                    throw new UnauthorizedException('You have to be logged in to access own-posts');
                }
            } // If not user 'session' and also not numeric
            elseif (is_numeric($params['user'])) {
                $filterParams['user_id'] = (int)$params['user'];
            } else {
                // Exception message in ClientListFilterProvider.php
                throw new InvalidClientFilterException('Invalid filter format "user".');
            }
        }
        // Filter: include deleted records
        if (isset($params['include-deleted']) && (int)$params['include-deleted'] === 1){
            unset($filterParams['deleted_at']);
        }
        // Other filters here

        // If there is no filter, all posts should be returned
        return $this->clientFinder->findClientsWithAggregates($filterParams);
    }
}