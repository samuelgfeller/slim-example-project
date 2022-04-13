<?php


namespace App\Domain\Post\Service;


use App\Domain\Exceptions\UnauthorizedException;
use App\Domain\Post\Data\UserPostData;
use App\Domain\Post\Exception\InvalidPostFilterException;
use Odan\Session\SessionInterface;
use Slim\Exception\HttpUnauthorizedException;

class PostFilterFinder
{
    public function __construct(
        private PostFinder $postFinder,
        private SessionInterface $session,
    )
    {
    }

    /**
     * Return posts matching given filter.
     * If there is no filter, all posts are returned.
     *
     * @param array $params GET parameters containing filter values
     *
     * @return UserPostData[]
     */
    public function findPostsWithFilter(array $params): array
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
                throw new InvalidPostFilterException('Filter "user" is not numeric.');
            }
            return $this->postFinder->findAllPostsFromUser((int)$params['user']);
        }
        // Other filters here

        // If there is no filter, all posts should be returned
        return $this->postFinder->findAllPostsWithUsers();
    }
}