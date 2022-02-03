<?php


namespace App\Domain\Post\Service;


use App\Domain\Post\Data\UserPostData;
use App\Domain\Post\Exception\InvalidPostFilterException;

class PostFilterFinder
{
    public function __construct(
        private PostFinder $postFinder
    ) { }

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
        if (isset($params['user'])){
            if (!is_numeric($params['user'])) {
                // Exception message tested in PostFilterProvider.php
                throw new InvalidPostFilterException('Filter "user" is not numeric.');
            }
            $userPosts = $this->postFinder->findAllPostsFromUser((int)$params['user']);
        }else {
            // If there is no filter, all posts should be returned
            $userPosts = $this->postFinder->findAllPostsWithUsers();
        }

        return $userPosts;
    }
}