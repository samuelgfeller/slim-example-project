<?php


namespace App\Domain\Post\Service;


use App\Domain\Post\Data\PostData;
use App\Domain\Post\Data\UserPostData;
use App\Domain\User\Service\UserFinder;
use App\Infrastructure\Post\PostFinderRepository;

class PostFinder
{
    public function __construct(
        private PostFinderRepository $postFinderRepository,
        private PostUserRightSetter $postUserRightSetter,
    ) { }

    /**
     * Gives all undeleted posts from db with name of user
     *
     * @return PostData[]
     */
    public function findAllPostsWithUsers(): array
    {
        $allPosts = $this->postFinderRepository->findAllPostsWithUsers();
        // In PHP, an object variable doesn't contain the object itself as value. It only contains an object identifier
        // meaning the reference is passed and changes are made on the original reference that can be used further
        // https://www.php.net/manual/en/language.oop5.references.php; https://stackoverflow.com/a/65805372/9013718
        $this->postUserRightSetter->setUserRightsOnPosts($allPosts);
        return $allPosts;
    }

    /**
     * Find one post in the database
     *
     * @param $id
     * @return PostData
     */
    public function findPost($id): PostData
    {
        return $this->postFinderRepository->findPostById($id);
    }

    /**
     * Return all posts which are linked to the given user
     *
     * @param int $userId
     * @return UserPostData[]
     */
    public function findAllPostsFromUser(int $userId): array
    {
        $allPosts = $this->postFinderRepository->findAllPostsByUserId($userId);
        $this->postUserRightSetter->setUserRightsOnPosts($allPosts);
        return $allPosts;
    }
}