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
        // Filter own posts
        if (isset($params['scope']) && $params['scope'] === 'own'){
            // User should be logged in (user has to be logged in to access own-posts)
            if(($userId = $this->session->get('user_id')) !== null){
                return $this->postFinder->findAllPostsFromUser((int)$userId);
            }
            throw new UnauthorizedException('You have to be logged in to access own-posts');
        }
        // Filter 'user'
        if (isset($params['user'])) {
            if (!is_numeric($params['user'])) {
                // Exception message tested in PostFilterProvider.php
                throw new InvalidPostFilterException('Filter "user" is not numeric.');
            }
            return $this->postFinder->findAllPostsFromUser((int)$params['user']);
        }

        // If there is no filter, all posts should be returned
        return $this->postFinder->findAllPostsWithUsers();
    }
}