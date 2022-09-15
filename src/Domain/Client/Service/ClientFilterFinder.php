<?php


namespace App\Domain\Client\Service;


use App\Domain\Client\Data\ClientResultDataCollection;
use App\Domain\Exceptions\UnauthorizedException;
use App\Domain\Note\Exception\InvalidNoteFilterException;
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
        // Filter 'user'
        if (isset($params['user'])) {
            // To display own posts, the client sends the filter user=session
            if ($params['user'] === 'session'){
                // User has to be logged-in to access own-posts
                if(($userId = $this->session->get('user_id')) !== null){
                    $params['user'] = $userId;
                } else {
                    throw new UnauthorizedException('You have to be logged in to access own-posts');
                }
            } // If not user 'session' and also not numeric
            elseif (!is_numeric($params['user'])) {
                // Exception message tested in PostFilterProvider.php
                throw new InvalidNoteFilterException('Filter "user" is not numeric.');
            }
            return $this->clientFinder->findAllClientsFromUser((int)$params['user']);
        }
        // Other filters here

        // If there is no filter, all posts should be returned
        return $this->clientFinder->findAllClientsWithAggregate();
    }
}